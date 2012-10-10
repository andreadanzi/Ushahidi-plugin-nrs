#!/usr/bin/python

import os, sys, threading, mosquitto, MySQLdb, logging, re, csv, hashlib, time, shutil
import settings

class GibeToNrsThread(threading.Thread):
  def __init__(self,e_uid,n_uid, csv_folder,logger):
    threading.Thread.__init__(self)
    self.logger = logger
    self.csv_folder = csv_folder
    self.env_uid = e_uid
    self.n_uid = n_uid
    if( not os.path.isdir(self.csv_folder) ):
      raise IOError("CSV Folder %s does not exists" % self.csv_folder)
    os.chdir(self.csv_folder)
    self.nrs_environment_id = self.check_env_uid()
    self.iRun = 0
    self.shutdown = False
  
  def run(self):
    self.logger.info("thread started on %s is starting up" % (self.csv_folder))
    try:
      while os.path.exists(self.csv_folder):
        self.read_csv_folder()
    except SyntaxError, e:
      self.logger.error( "Error: %s" % e )
      self.shutdown = True;
    self.logger.info("thread started on %s is shutting down with iRun=%d" % (self.csv_folder,self.iRun))
  
  def read_csv_folder(self):
    time.sleep(5)
    if os.path.exists(self.csv_folder) == True:
	    for files in os.listdir(self.csv_folder):
	      if os.path.isdir(self.csv_folder+"/"+files) == True:
		continue
	      self.logger.info("thread on %s founds file %s" % (self.csv_folder,files) )
	      sha256sum = self.sha256Checksum(self.csv_folder+"/"+files)
	      bulk_insert = {}
	      node_uid = self.n_uid
	      nrs_node_id = self.check_node_uid(self.nrs_environment_id, self.env_uid+node_uid)
	      bulk_insert_row = []
	      datastream_uids = {}
	      sample=1      
              i=0
	      sUpdated = time.strftime('%Y-%m-%d %H:%M:%S')
	      with open(self.csv_folder+"/"+files, 'rb') as csvfile:
		csv_reader = csv.reader(csvfile, delimiter='\t')  
		for row in csv_reader:
		  #self.logger.info("thread on %s reads CSV sample number %d" % (self.csv_folder,sample) )
		  icol=0
		  isensor=1
		  ds_prefix="_%02d."
		  ds_prefix_no = 1
		  sAt=""
		  slast_value="0.0"
		  for col in row:
		    icol=icol+1
		    if icol==1:
		      # first col is date
		      sdate = "%s" % col
		    elif icol==2:
		      # time
		      stime = "%s" % col
		      dt=time.strptime(sdate + " " +stime,"%d/%m/%Y %H:%M:%S")
	      	      sAt = time.strftime('%Y%m%d%H%M%S000000',dt)           
		    elif icol > 2:
		      current_value = "%s" % col
		      current_value = current_value.replace(',','.')
		      first_part_current_value = current_value.split('.')[0]
		      first_part_slast_value = slast_value.split('.')[0]
		      if int(first_part_slast_value) >= int(first_part_current_value):
		         ds_prefix_no = ds_prefix_no+1
                         isensor = 1
		      prefix = ds_prefix % ds_prefix_no
		      #datastream_uid = prefix + first_part_current_value
                      datastream_uid = prefix + "%02d" % (isensor)
		      if sample==1:
		        nrs_datastream_id = self.check_datastream_uid(self.nrs_environment_id,nrs_node_id,self.env_uid+node_uid+datastream_uid)
		        datastream_uids[icol]=nrs_datastream_id
		      else:
		        nrs_datastream_id = datastream_uids[icol]
		      bulk_insert_row.append((self.nrs_environment_id, nrs_node_id,nrs_datastream_id,sample,current_value,sAt,sUpdated  ))
		      i=i+1
                      isensor = isensor + 1
		      slast_value = current_value
		  sample=sample+1
              self.logger.info("bulk_insert_row is ready with %d samples and %d rows" % (sample,i))
              if not os.path.exists(self.csv_folder+"/tmp"):
                os.mkdir(self.csv_folder+"/tmp")
	      csv_file = time.strftime('%Y%m%d%H%M%S')
              with open(self.csv_folder+"/tmp/" + csv_file + ".csv", 'wb') as importcsvfile:
                writer = csv.writer(importcsvfile,delimiter='|')
                writer.writerows(bulk_insert_row)
              self.logger.info("File %s written" % (self.csv_folder+"/tmp/" + csv_file + ".csv"))
              sQuery = """LOAD DATA INFILE '%s' 
                          INTO TABLE nrs_datapoint 
                          FIELDS TERMINATED BY '|' 
                          LINES TERMINATED BY '\r\n' 
                          ( 
                             nrs_environment_id, 
                             nrs_node_id, 
                             nrs_datastream_id, 
                             sample_no, 
                             value_at, 
                             datetime_at, 
                             updated
                          );""" % (self.csv_folder+"/tmp/" + csv_file + ".csv")
	      #self.logger.info("sQuery = %s" % sQuery)
              retVal = self.sql_execute( sQuery )
	      saved_folder = time.strftime('%Y%m%d%H%M')
              if not os.path.exists(self.csv_folder+"/"+saved_folder):
	        os.mkdir(self.csv_folder+"/"+saved_folder)
	      shutil.move(self.csv_folder+"/"+files,self.csv_folder+"/"+saved_folder)
	      self.logger.info("thread on %s moved file %s into %s" % (self.csv_folder,files,saved_folder) )
	      mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
	      mysql_cur = mysql_conn.cursor()
	      sQuery = """INSERT INTO nrs_csv_client (folder,file_name,sha256sum,noitems,saved_folder) VALUES ('%s','%s','%s',%d,'%s')""" % (self.csv_folder,files,sha256sum,i,saved_folder)
	      try:
		mysql_cur.execute(sQuery)
		mysql_conn.commit()
	      except MySQLdb.Error, e:
		self.logger.error("An error has been passed. %s" %e  )
		mysql_conn.rollback()    
	      mysql_conn.close()


  def check_env_uid(self):
    return_value = 0
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    sQuery = """SELECT id FROM nrs_environment WHERE environment_uid = '%s' """ % self.env_uid
    retVal= mysql_cur.execute(sQuery)
    if retVal > 0L :
      row = mysql_cur.fetchone()  
      return_value = row[0]
      #self.logger.info("Fetched Environment with ID %d and UID %s" % (return_value,self.env_uid))
    else:
      sUpdated = time.strftime('%Y-%m-%d %H:%M:%S')
      sQuery = """INSERT INTO nrs_environment (title,environment_uid,status,updated) VALUES ('%s','%s',%d,'%s')""" % ("Environment with UID " + self.env_uid,self.env_uid,4,sUpdated)
      retVal= mysql_cur.execute(sQuery)
      mysql_conn.commit()
      sQuery = """SELECT id FROM nrs_environment WHERE environment_uid = '%s' """ % self.env_uid
      retVal= mysql_cur.execute(sQuery)
      if retVal > 0 :
        row = mysql_cur.fetchone()  
        return_value = row[0]
        #self.logger.info("Inserted Environment with ID %d and UID %s" % (return_value,self.env_uid))
    mysql_conn.close()
    return return_value


  def check_node_uid(self,nrs_environment_id, node_uid):
    return_value = 0
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    sQuery = """SELECT id FROM nrs_node WHERE nrs_environment_id = %d AND node_uid='%s' """ % (nrs_environment_id, node_uid)
    retVal= mysql_cur.execute(sQuery)
    if retVal > 0 :
      row = mysql_cur.fetchone()  
      return_value = row[0]
    else:
      sUpdated = time.strftime('%Y-%m-%d %H:%M:%S')
      sQuery = """INSERT INTO nrs_node (title,nrs_environment_id,node_uid,status,updated) VALUES ('%s',%d,'%s',%d,'%s')""" % ("Node with UID " + node_uid,nrs_environment_id,node_uid,4,sUpdated)
      retVal= mysql_cur.execute(sQuery)
      mysql_conn.commit()
      sQuery = """SELECT id FROM nrs_node WHERE nrs_environment_id = %d AND node_uid='%s' """ % (nrs_environment_id, node_uid)
      retVal= mysql_cur.execute(sQuery)
      if retVal > 0 :
        row = mysql_cur.fetchone()  
        return_value = row[0]
    mysql_conn.close()
    return return_value

  def check_datastream_uid(self, nrs_environment_id,nrs_node_id,key):
    return_value = 0
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    sQuery = """SELECT id FROM nrs_datastream WHERE nrs_environment_id = %d AND nrs_node_id =%d AND datastream_uid='%s' """ % (nrs_environment_id,nrs_node_id, key)
    retVal= mysql_cur.execute(sQuery)
    if retVal > 0 :
      row = mysql_cur.fetchone()  
      return_value = row[0]
      #self.logger.info("Fetched Datastream with ID %d and UID %s" % (return_value,key))
    else:
      sUpdated = time.strftime('%Y-%m-%d %H:%M:%S')
      sQuery = """INSERT INTO nrs_datastream (title,nrs_environment_id,nrs_node_id,datastream_uid,updated) VALUES ('%s',%d,%d,'%s','%s')""" % ("Datastream with UID " + key,nrs_environment_id,nrs_node_id,key,sUpdated)
      retVal= mysql_cur.execute(sQuery)
      mysql_conn.commit()
      sQuery = """SELECT id FROM nrs_datastream WHERE nrs_environment_id = %d AND nrs_node_id =%d AND datastream_uid='%s' """ % (nrs_environment_id,nrs_node_id, key)
      retVal= mysql_cur.execute(sQuery)
      if retVal > 0 :
        row = mysql_cur.fetchone()  
        return_value = row[0]
        #self.logger.info("Inserted Datastream with ID %d and UID %s" % (return_value,key))
    mysql_conn.close()
    return return_value

  def sql_execute(self, sQuery):
    retVal = 0
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    try:
      retVal = mysql_cur.execute(sQuery)
      mysql_conn.commit()
    except MySQLdb.Error, e:
      self.logger.error("An error has been passed. %s %s" % (e, sQuery) )
      mysql_conn.rollback() 
    mysql_conn.close()
    return retVal

  def sha256Checksum(self,filePath):
    fh = open(filePath, 'rb')
    m = hashlib.sha256()
    while True:
      data = fh.read(8192)
      if not data:
          break
      m.update(data)
    return m.hexdigest()

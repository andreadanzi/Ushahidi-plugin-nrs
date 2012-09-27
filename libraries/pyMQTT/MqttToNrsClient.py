#!/usr/bin/python

import os, sys, threading, mosquitto, MySQLdb, logging
import settings

class MqttToNrsClient:
  def __init__(self, nrs_item,logger):
    self.iRun = -1
    self.logger = logger
    self.nrs_item = nrs_item
    self.shutdown = False
    self.client = mosquitto.Mosquitto(self.nrs_item['name'],clean_session=False, obj=self.iRun)
    self.client.on_message = self.on_mqtt_message
    self.client.on_connect = self.on_mqtt_connect
    self.client.on_disconnect = self.on_mqtt_disconnect
    self.client.connect(self.nrs_item['host'],int(self.nrs_item['port']))
    self.set_subscription_status(2)
    try:
      while self.iRun == -1:
        self.client.loop()
        # bisogna verificare lo stato ogni tot secondi self.check_subscription_status()
    except Exception, e:
      self.logger.error( "Error: %s" % e )
    self.client.disconnect()
    self.shutdown = True;
    self.set_subscription_status(3)  
  def on_mqtt_connect(self, client, obj, rc):  
    if rc != 0:
      exit(rc)
    else:
      self.logger.info("Client %s connected to %s:%s %s" % (self.nrs_item['name'],self.nrs_item['host'],self.nrs_item['port'],self.nrs_item['topic']))
      self.client.subscribe(self.nrs_item['topic'],self.nrs_item['qos'])
  def on_mqtt_message(self, client, obj, msg):
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    try:
      sQuery = """INSERT INTO nrs_mqtt_message ( nrs_mqtt_subscription_id , mqtt_mid , mqtt_topic , mqtt_payload , mqtt_payloadlen , mqtt_qos, mqtt_retain ) VALUES (%d,%d,'%s','%s',%d,%d,%d)""" % (self.nrs_item['id'],msg.mid,msg.topic,msg.payload,len(msg.payload),msg.qos,msg.retain)
      # GEstire duplicati in caso di Retain a 1
      # self.logger.info( "sQuery = " + sQuery )
      mysql_cur.execute(sQuery)
      mysql_conn.commit()
    except MySQLdb.Error, e:
      self.logger.error( "An error has been passed. %s" % e )
      mysql_conn.rollback()
    mysql_conn.close()
    self.logger.info( "%s - msg.topic=%s msg.qos=%d msg.payloadlen=%d msg.retain=%d" % (self.nrs_item['name'],msg.topic,msg.qos,len(msg.payload),msg.retain) )
  def on_mqtt_disconnect(self, mosq, obj, rc):
    obj = rc
    self.logger.info( "%s disconnected!" % self.nrs_item['name'] )
    self.set_subscription_status(1)  
  def set_subscription_status(self,status):
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    sQuery = """UPDATE nrs_mqtt_subscription SET mqtt_subscription_active=%d WHERE id=%d""" % (status,self.nrs_item['id'])
    try:
      mysql_cur.execute(sQuery)
      mysql_conn.commit()
    except MySQLdb.Error, e:
      self.logger.error("An error has been passed. %s" %e  )
      mysql_conn.rollback()    
    mysql_conn.close()
  def check_subscription_status(self):
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    res = mysql_cur.execute("""SELECT mqtt_subscription_active FROM nrs_mqtt_subscription WHERE id=%d""" % self.nrs_item['id'] )
    row = mysql_cur.fetchone()
    mysql_conn.close()
    if( row[0] != 2 ):
      self.iRun = 2

class MqttToNrsThread(threading.Thread):
  def __init__(self, nrs_item,logger,status_queue):
    threading.Thread.__init__(self)
    self.logger = logger
    self.status_queue = status_queue
    self.iRun = -1
    self.nrs_item = nrs_item
    self.shutdown = False
    self.client = mosquitto.Mosquitto(self.nrs_item['name'],clean_session=False, obj=self.iRun)
    self.client.on_message = self.on_mqtt_message
    self.client.on_connect = self.on_mqtt_connect
    self.client.on_disconnect = self.on_mqtt_disconnect
    self.client.connect(self.nrs_item['host'],int(self.nrs_item['port']))
  
  def run(self):
    self.logger.info("thread started %s - client %s:%s %s" % (self.nrs_item['name'],self.nrs_item['host'],self.nrs_item['port'],self.nrs_item['topic']))
    self.set_subscription_status(2)
    try:
      while self.iRun == -1:
        self.client.loop()
        if( not self.status_queue.empty()):
           intStatus = self.status_queue.get()
           if(intStatus != 2):
               self.client.disconnect()
               self.set_subscription_status(intStatus)
               self.shutdown = True;
               self.iRun = 3
    except Exception, e:
      self.logger.error( "Error: %s" % e )
      self.client.disconnect()
      self.shutdown = True;
  
  def on_mqtt_connect(self, client, obj, rc):  
    if rc != 0:
      exit(rc)
    else:
      self.client.subscribe(self.nrs_item['topic'],self.nrs_item['qos'])
  
  def on_mqtt_message(self, client, obj, msg):
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    try:
      sQuery = """INSERT INTO nrs_mqtt_message ( nrs_mqtt_subscription_id , mqtt_mid , mqtt_topic , mqtt_payload , mqtt_payloadlen , mqtt_qos, mqtt_retain ) VALUES (%d,%d,'%s','%s',%d,%d,%d)""" % (self.nrs_item['id'],msg.mid,msg.topic,msg.payload,len(msg.payload),msg.qos,msg.retain)
      # GEstire duplicati in caso di Retain a 1
      # self.logger.info( "sQuery = " + sQuery )
      mysql_cur.execute(sQuery)
      mysql_conn.commit()
    except MySQLdb.Error, e:
      self.logger.error( "An error has been passed. %s" %e )
      mysql_conn.rollback()
    mysql_conn.close()
    self.logger.info( "%s - msg.topic=%s msg.qos=%d msg.payloadlen=%d msg.retain=%d" % (self.nrs_item['name'],msg.topic,msg.qos,len(msg.payload),msg.retain) )
  
  def on_mqtt_disconnect(self, mosq, obj, rc):
    self.logger.info( "%s disconnected!" % self.nrs_item['name'])
    obj = rc
  
  def set_subscription_status(self,status):
    self.logger.info("Status of client %s will be set to %d" % (self.nrs_item['name'],status ) )
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    sQuery = """UPDATE nrs_mqtt_subscription SET mqtt_subscription_active=%d WHERE id=%d""" % (status,self.nrs_item['id'])
    try:
      mysql_cur.execute(sQuery)
      mysql_conn.commit()
    except MySQLdb.Error, e:
      self.logger.error("An error has been passed. %s" %e  )
      mysql_conn.rollback()    
    mysql_conn.close()

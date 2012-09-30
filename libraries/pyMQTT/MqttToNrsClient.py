#!/usr/bin/python

import os, sys, threading, mosquitto, MySQLdb, logging, re
import settings

def ParseTopic(sTopicString):
  retItem = {'type':-1,'action':'','len':0,'entity_uid':''}
  # clean topic if wrong
  if(len(sTopicString)>1 and sTopicString[len(sTopicString)-1]=="/"):
    sTopicString = sTopicString[0:len(sTopicString)-1]
  splitted = sTopicString.split("/")
  iLen = len(splitted)
  if(iLen>4):
    if( splitted[1] != "nrs" or  splitted[2] != "v1"  or  splitted[3] != "env" ):
      raise SyntaxError("Wrong add env mqtt topic format (%s)" % sTopicString)
    if(iLen==5): # New Environment 
      if( splitted[iLen-1]=='delete' ):
        raise SyntaxError("Wrong add Environment mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 1
      retItem['action'] = 'a'
      retItem['env_uid'] = splitted[4].split('.')[0] # remove .csv
      retItem['entity_uid'] = splitted[4].split('.')[0] # remove .csv
    elif(iLen==6): # Delete Environment
      retItem['type'] = 1
      retItem['env_uid'] = splitted[5].split('.')[0] # remove .csv
      retItem['entity_uid'] = splitted[5].split('.')[0] # remove .csv
      if(splitted[iLen-2] == 'delete'):
        retItem['action'] = 'd'
      else:
        raise SyntaxError("Wrong delete env mqtt topic format (%s)" % sTopicString)
    elif(iLen==7): # New Node
      if( splitted[5] != "node" or splitted[iLen-1]=='delete' ):
        raise SyntaxError("Wrong add node mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 2
      retItem['action'] = 'a'
      retItem['env_uid'] = splitted[4]
      retItem['entity_uid'] = splitted[6].split('.')[0] # remove .csv
      retItem['node_uid'] = splitted[6].split('.')[0] # remove .csv
    elif(iLen==8): # Delete Node
      if( splitted[5] != "node" ):
        raise SyntaxError("Wrong delete node mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 2
      retItem['env_uid'] = splitted[4]
      retItem['entity_uid'] = splitted[7].split('.')[0] # remove .csv
      retItem['node_uid'] = splitted[7].split('.')[0] # remove .csv
      if(splitted[iLen-2]=='delete'):
        retItem['action'] = 'd'
      else:
        raise SyntaxError("Wrong delete node mqtt topic format (%s)" % sTopicString)
    elif(iLen==9): # New Datastream
      if( splitted[5] != "node" or splitted[7] != "datastream" or splitted[iLen-1]=='delete'):
        raise SyntaxError("Wrong add Datastream mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 3
      retItem['action'] = 'a'
      retItem['env_uid'] = splitted[4]
      retItem['node_uid'] = splitted[6]
      retItem['entity_uid'] = splitted[8].split('.')[0] # remove .csv
      retItem['datastream_uid'] = splitted[8].split('.')[0] # remove .csv
    elif(iLen==10): # Delete Datastream
      if( splitted[5] != "node" or splitted[7] != "datastream" ):
        raise SyntaxError("Wrong delete Datastream mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 3
      retItem['env_uid'] = splitted[4]
      retItem['node_uid'] = splitted[6]
      retItem['entity_uid'] = splitted[9].split('.')[0] # remove .csv
      retItem['datastream_uid'] = splitted[9].split('.')[0] # remove .csv
      if(splitted[iLen-2]=='delete'):
        retItem['action'] = 'd'
      else:
        raise SyntaxError("Wrong delete Datastream mqtt topic format (%s)" % sTopicString)
    elif(iLen==11): # New Datapoint
      if( splitted[5] != "node" or splitted[7] != "datastream" or splitted[9] != "datapoint" ):
        raise SyntaxError("Wrong add Datapoint mqtt topic format (%s)" % sTopicString)
      retItem['type'] = 4
      retItem['action'] = 'a'
      retItem['env_uid'] = splitted[4]
      retItem['node_uid'] = splitted[6]
      retItem['datastream_uid'] = splitted[8]
      retItem['entity_uid'] = splitted[10].split('.')[0] # remove .csv
      retItem['datapoint_uid'] = splitted[10].split('.')[0] # remove .csv
    else:
      raise SyntaxError("Wrong len (%d) of mqtt topic format (%s)" % (iLen,sTopicString))
  else:
    raise SyntaxError("Wrong mqtt topic format (%s), it's too short, len = %d" % (sTopicString,iLen))
  retItem['len'] = iLen
  return retItem

class MqttToNrsThread(threading.Thread):
  def __init__(self, nrs_item,logger,status_queue):
    threading.Thread.__init__(self)
    self.logger = logger
    self.status_queue = status_queue
    self.iRun = -1
    self.nrs_item = nrs_item
    self.shutdown = False
    self.client = mosquitto.Mosquitto(self.nrs_item['name'],clean_session=True, obj=self.iRun)
    self.client.on_message = self.on_mqtt_message
    self.client.on_connect = self.on_mqtt_connect
    self.client.connect(self.nrs_item['host'],int(self.nrs_item['port']))
  
  def run(self):
    self.logger.info("thread started %s - client %s:%s %s" % (self.nrs_item['name'],self.nrs_item['host'],self.nrs_item['port'],self.nrs_item['topic']))
    self.set_subscription_status(2)
    self.client.on_disconnect = self.on_mqtt_disconnect
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
    except SyntaxError, e:
      self.logger.error( "Error: %s" % e )
      self.client.disconnect()
      self.shutdown = True;
  
  def on_mqtt_connect(self, client, obj, rc):  
    if rc != 0:
      exit(rc)
    else:
      self.client.subscribe(self.nrs_item['topic'],self.nrs_item['qos'])
  
  def on_mqtt_message(self, client, obj, msg):
    mqtt_topic_errors = -1
    retItem = {'type':-1,'action':'','len':0,'entity_uid':''}
    try:
      retItem = ParseTopic(msg.topic)
      mqtt_topic_errors = 0
      #print retItem['entity_uid']
    except SyntaxError, e:
      self.logger.error( "SyntaxError during topic parsing on Client %s, Error=%s" % (self.nrs_item['name'], e) )
      mqtt_topic_errors = 1

    self.logger.info( "Messaggio di tipo %d associato all'azione %s per entity con uid=%s (errorcode=%d)" % (retItem['type'],retItem['action'],retItem['entity_uid'], mqtt_topic_errors))
    mysql_conn = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username,passwd=settings.password,db=settings.database)
    mysql_cur = mysql_conn.cursor()
    try:
      #sQuery = """INSERT INTO nrs_mqtt_message ( nrs_mqtt_subscription_id , mqtt_mid , mqtt_topic , mqtt_payload , mqtt_payloadlen , mqtt_qos, mqtt_retain ) VALUES (%d,%d,'%s','%s',%d,%d,%d)""" % (self.nrs_item['id'],msg.mid,msg.topic,msg.payload,len(msg.payload),msg.qos,msg.retain) 
      sQuery = """INSERT INTO nrs_mqtt_message ( nrs_mqtt_subscription_id , mqtt_mid , mqtt_topic , mqtt_payload , mqtt_payloadlen , mqtt_qos, mqtt_retain , nrs_entity_type, mqtt_topic_errors,  nrs_entity_uid, mqtt_nrs_action ) VALUES (%d,%d,'%s','%s',%d,%d,%d,%d,%d,'%s','%s')""" % (self.nrs_item['id'],msg.mid,msg.topic,msg.payload,len(msg.payload),msg.qos,msg.retain,retItem['type'],mqtt_topic_errors,retItem['entity_uid'],retItem['action'])
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

#!/usr/bin/python

import threading
import os
import subprocess
import socket
import sys
import time
from struct import *

import MySQLdb
import mosquitto

exitFlag = 0

hostname="127.0.0.1"
portnumber=3386
username="root"
password="rootroot"
database="ushahidi"

class nrsThread (threading.Thread):
    def __init__(self, threadID, name, item, mqtt_client):
        threading.Thread.__init__(self)
        self.threadID = threadID
        self.name = name
        self.item = item
	self.mqtt_client = mqtt_client
	def on_message(mqtt_client, obj, msg):
            mysql_conn = MySQLdb.connect(host=hostname, port=portnumber, user=username,passwd=password,db=database)
            mysql_cur = mysql_conn.cursor()
            try:
		sQuery = """INSERT INTO nrs_mqtt_message ( nrs_mqtt_subscription_id , mqtt_mid , mqtt_topic , mqtt_payload , mqtt_payloadlen , mqtt_qos, mqtt_retain ) VALUES (%d,%d,'%s','%s',%d,%d,%d)""" % (self.item['id'],msg.mid,msg.topic,msg.payload,len(msg.payload),msg.qos,msg.retain)
		# GEstire duplicati in caso di Retain a 1
		print "sQuery = " + sQuery
                mysql_cur.execute(sQuery)
                mysql_conn.commit()
            except MySQLdb.Error, e:
                print "An error has been passed. %s \n" %e 
                mysql_conn.rollback()
            mysql_conn.close()
            print( self.name + " - Message received on topic "+msg.topic+" with QoS "+str(msg.qos)+" and payload "+msg.payload +"\n")
        self.mqtt_client.on_message = on_message
    def run(self):
        print "Starting " + self.name
	irun = -1
        # print_time(self.name, self.item, 5)
	self.mqtt_client.connect(self.item['host'], int(self.item['port']))
	self.mqtt_client.subscribe(self.item['topic'],self.item['qos'])
	while irun == -1:
	    self.mqtt_client.loop()
        print "Exiting " + self.name


db = MySQLdb.connect(host=hostname, port=portnumber, user=username,passwd=password,db=database)
c = db.cursor()
res = c.execute("""SELECT * FROM nrs_mqtt_subscription WHERE mqtt_subscription_active=1""")
rows = c.fetchall()
list_items = []
dict_item = {}
for row in rows:
  dict_item={'id':row[0],'name':row[1],'topic':row[3],'host':row[4],'port':row[5],'sub_id':row[6],'active':row[7],'username':row[8],'password':row[9],'qos':row[13]}
  list_items.append(dict_item)

mosquittosList = []
for item in list_items:
  runObj = -1
  mqtt_client = mosquitto.Mosquitto(client_id=item['name'], clean_session=False, obj=runObj)
  nrsMqtt = nrsThread(item['id'], item['name'], item ,mqtt_client)
  nrsMqtt.setDaemon(True)
  mosquittosList.append(nrsMqtt)
  nrsMqtt.start()


print "Exiting Main Thread\n"
# mqtt_subscription_name varchar(250) NOT NULL,1
# mqtt_subscription_color varchar(20) DEFAULT 'CC0000',2
# mqtt_subscription_topic varchar(255) NOT NULL, 3
# mqtt_host varchar(255) DEFAULT NULL,4
# mqtt_port varchar(255) DEFAULT NULL,5
# mqtt_subscription_id varchar(250) DEFAULT NULL,6
# mqtt_subscription_active tinyint(4) NOT NULL DEFAULT 1,7
# mqtt_username varchar(255) DEFAULT NULL,8
# mqtt_password varchar(255) DEFAULT NULL,9
# mqtt_will_topic varchar(255) DEFAULT NULL,10
# mqtt_will_payload text DEFAULT NULL,11
# mqtt_will_retain tinyint(4) DEFAULT NULL,12
# mqtt_qos tinyint(4) DEFAULT '0',13

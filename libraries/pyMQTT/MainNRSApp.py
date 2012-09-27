# To kick off the script, run the following from the python directory:
#   PYTHONPATH=`pwd` python MainNRSApp.py start

#standard python libs
import logging
import time
import MySQLdb
from Queue import Queue

import settings

#third party libs
from daemon import runner
from MqttToNrsClient import MqttToNrsClient
from MqttToNrsClient import MqttToNrsThread

class MainNRSApp():
   
    def __init__(self):
        self.stdin_path = '/dev/null'
        self.stdout_path = '/dev/tty'
        self.stderr_path = '/dev/tty'
        self.pidfile_path =  settings.pidfile_path
        self.pidfile_timeout = 5
        self.mqtt_thread_dict = {}
           
    def run(self):
        while True:
            # connects to DB for fetching active subscriptions active or already started
            db = MySQLdb.connect(host=settings.hostname, port=settings.portnumber, user=settings.username, passwd=settings.password, db=settings.database)
            c = db.cursor()
            res = c.execute("""SELECT * FROM nrs_mqtt_subscription WHERE mqtt_subscription_active=1 OR mqtt_subscription_active=2""")
            rows = c.fetchall()
            db.close()
            # Check if there are existinf thread that should be stopped
	    for key in self.mqtt_thread_dict.keys():
              key_found = False
              for row in rows:
                if(row[0]==key):
                  key_found = True
                  break
              if(not key_found):
                item_queue = self.mqtt_thread_dict[key]['queue']
                item_queue.put(3)
                removed_item = self.mqtt_thread_dict.pop(key)
            for row in rows:
              if( row[7]==1 ):
                dict_item={'id':row[0],'name':row[1],'topic':row[3],'host':row[4],'port':row[5],'sub_id':row[6],'active':row[7],'username':row[8],'password':row[9],'qos':row[13]}
                logger.info( "nrs_mqtt_subscription with id=%d and name='%s' found" % (dict_item['id'], dict_item['name']) )
                status_q = Queue()
                client = MqttToNrsThread(dict_item,logger,status_q)
                self.mqtt_thread_dict[row[0]] = {'item':dict_item, 'client':client, 'queue':status_q}
                client.start()
            time.sleep(3)

app = MainNRSApp()
logger = logging.getLogger("MainNRSAppLog")
logger.setLevel(logging.INFO)
formatter = logging.Formatter("%(asctime)s - %(name)s - %(levelname)s - %(message)s")
handler = logging.FileHandler(settings.logfile_path)
handler.setFormatter(formatter)
logger.addHandler(handler)

daemon_runner = runner.DaemonRunner(app)
#This ensures that the logger file handle does not get closed during daemonization
daemon_runner.daemon_context.files_preserve=[handler.stream]
daemon_runner.do_action()

# To kick off the script, run the following from the python directory:
#   PYTHONPATH=`pwd` python GibeNRSApp.py start

#standard python libs
import logging, os
import time
import MySQLdb
from sets import Set

import settings

#third party libs
from daemon import runner
from GibeToNrsClient import GibeToNrsThread

class GibeNRSApp():
   
    def __init__(self):
        self.stdin_path = '/dev/null'
        self.stdout_path = '/dev/tty'
        self.stderr_path = '/dev/tty'
        self.pidfile_path =  settings.gibepidfile_path
        self.pidfile_timeout = 5
        self.dir_set = Set('xyz')
        self.e_uid = settings.environment_uid
           
    def run(self):
        logger.info("GibeNRSApp Started on %s" % settings.gibeimportfolder_path)
        while True:           
            # connects to DB for fetching active subscriptions active or already started          
            for item in os.listdir(settings.gibeimportfolder_path):
                if os.path.isdir(settings.gibeimportfolder_path+"/"+item) == True and item not in self.dir_set:
                  self.dir_set.add(item)
                  csv_folder = settings.gibeimportfolder_path+"/"+item
                  client = GibeToNrsThread(self.e_uid,item,csv_folder,logger)
                  client.start()
            
            time.sleep(10)

app = GibeNRSApp()
logger = logging.getLogger("GibeNRSAppLog")
logger.setLevel(logging.INFO)
formatter = logging.Formatter("%(asctime)s - %(name)s - %(levelname)s - %(message)s")
handler = logging.FileHandler(settings.gibelogfile_path)
handler.setFormatter(formatter)
logger.addHandler(handler)

daemon_runner = runner.DaemonRunner(app)
#This ensures that the logger file handle does not get closed during daemonization
daemon_runner.daemon_context.files_preserve=[handler.stream]
daemon_runner.do_action()

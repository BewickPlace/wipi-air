# This script will wait for a button to be pressed and then shutdown
# the Raspberry Pi.
# The button is to be connected on header 5 between pins 6 and 8.

# http://kampis-elektroecke.de/?page_id=3740
# http://raspi.tv/2013/how-to-use-interrupts-with-python-on-the-raspberry-pi-and-rpi-gpio
# https://pypi.python.org/pypi/RPi.GPIO

import RPi.GPIO as GPIO
import time
import os
import socket
import fcntl
import struct
import logging
import subprocess
import datetime

logging.basicConfig(filename='/var/log/monitor.log', level=logging.DEBUG, format='%(asctime)s %(message)s')
logging.info('WiPi-Air monitor process commenced')

# we will use the pin numbering of the SoC, so our pin numbers in the code are 
# the same as the pin numbers on the gpio headers
GPIO.setmode(GPIO.BOARD)
GPIO.setwarnings(False) 

# We choose pin 5 as this is also used for reset - to start from Halt
# This allows our switch just to be connected to Pin5 & Ground (pin 6).
#
# Pin 5 (Header 1) will be input and will have his pull up resistor activated
# so we only need to connect a button to ground.
#
# We also use pin 7 as a way to show Pi active for switch illumination (allow startup to complete)
#
GPIO.setup(5, GPIO.IN, pull_up_down = GPIO.PUD_UP)  
#time.sleep(15)
GPIO.setup(7, GPIO.OUT, initial=GPIO.HIGH)
LED = True
shutdown = False
flash = False
sleeptime = 1
restartenabled = False
networkerror = 99		# Assumes network will start unconnected
networktimer = 0
now = datetime.datetime.now()
lastmonth = now.month

# ISR: if our button is pressed, we will have a falling edge on pin 31
# this will trigger this interrupt:
def Int_shutdown(channel):  
	global shutdown
	global flash
	global sleeptime
	# shutdown our Raspberry Pi
	# print "Ready to Shutdown"
        logging.info('User requested shutdown (button)')
	shutdown = True
	flash = True
	sleeptime = 1
        try: os.remove('/forcefsck')
        except: pass
	os.system("sudo shutdown -h now")
#
# Check Log Status & Putge key logs each month (no longer called)
#
def check_logstatus():
	global lastmonth

	now = datetime.datetime.now()
	currentmonth = now.month
	if lastmonth != currentmonth: 
		os.system('sudo sh -c "cat /dev/null > /var/log/auth.log"')
		os.system('sudo sh -c "cat /dev/null > /var/log/kern.log"')
		os.system('sudo sh -c "cat /dev/null > /var/log/daemon.log"')
		os.system('sudo sh -c "cat /dev/null > /var/log/messages"')
		os.system('sudo sh -c "cat /dev/null > /var/log/syslog"')
		lastmonth = currentmonth
		logging.info("Logfile Monthly Purge Complete")

#
# Force the WiPi-Air to Restart when file exists
# 
def check_restart():  
	global shutdown
	global flash
	global sleeptime

#	print "checking restart"
        if os.path.isfile('/var/www/restart.force-restart'):
	   # shutdown our Raspberry Pi
           logging.info('User requested restart')
	   shutdown = True
	   time.sleep(2)
	   flash = True
	   sleeptime = 1
	   os.system("sudo shutdown -r now")
        if os.path.isfile('/var/www/restart.force-shutdown'):
	   # shutdown our Raspberry Pi
           logging.info('User requested shutdown')
	   shutdown = True
	   time.sleep(2)
	   flash = True
	   sleeptime = 1
	   os.system("sudo shutdown -h now")
        if os.path.isfile('/var/www/restart.network-restart'):
	   # restart Wireless Network
           logging.info('User requested WiFi Network restart')
           try: os.remove('/var/www/restart.network-restart')
           except: pass
	   GPIO.output(7, GPIO.LOW)
	   flash = True
	   os.system("sudo ifdown wlan0")
	   time.sleep(5)
	   os.system("sudo ifup wlan0")
        if os.path.isfile('/var/www/restart.shairport-restart'):
	   # restart shairport
           logging.info('User requested Shairport restart')
           try: os.remove('/var/www/restart.shairport-restart')
           except: pass
	   os.system("sudo systemctl restart shairport.service")
        if os.path.isfile('/var/www/restart.raspotify-restart'):
	   # restart raspotify
           logging.info('User requested Raspotify restart')
           try: os.remove('/var/www/restart.raspotify-restart')
           except: pass
	   os.system("sudo systemctl restart raspotify.service")
        if os.path.isfile('/var/www/restart.daapd-restart'):
	   # restart forked-daapd
           logging.info('User requested Daapd restart')
           try: os.remove('/var/www/restart.daapd-restart')
           except: pass
	   os.system("sudo /etc/init.d/forked-daapd restart")
        if shutdown:
	   # we are shutting down the Pi - remove forced disk check
           try: os.remove('/forcefsck')
           except: pass

#
# Check the network interfaces - if IP address exists then assume network connected
#
def if_connected(ifname1, ifname2):
    global networkerror
    global networktimer

    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)

    try:
       a = socket.inet_ntoa(fcntl.ioctl(
           s.fileno(),
           0x8915,  # SIOCGIFADDR
           struct.pack('256s', ifname1[:15])
       )[20:24])
#       print "Wlan Connected"
       return True
    except socket.error as i1:
       pass;
    except IOError as i1:
       pass;
    except:
       logging.info("Unknown network error: %s", ifname1)

    try:
       a = socket.inet_ntoa(fcntl.ioctl(
           s.fileno(),
           0x8915,  # SIOCGIFADDR
           struct.pack('256s', ifname2[:15])
       )[20:24])
#       print "LAN Connected"
       return True
    except socket.error as i2:
       pass
    except IOError as i2:
       pass
    except:
       logging.info("Unknown network error: %s", ifname2)


    if i1[0] != networkerror :
        networkerror = i1[0]
	logging.debug('Network Down error (%d:%d) %s:%s', i1[0], i2[0], ifname1, i1[1])
        networktimer = 0


    networktimer = networktimer + 1;
    if networktimer > 9000 :
	networktimer = 0
        logging.debug('...resetting wlan network')
        plugstatus = subprocess.check_output(['ifdown', '--force', 'wlan0'])
        time.sleep(2)
        plugstatus = subprocess.check_output(['ifup', 'wlan0'])

    return False

#
#	We should check if network connection
#
def check_network():
	global flash
	global sleeptime
        global networkerror


	if if_connected('wlan0', 'eth0'):
#	   print "Network Connected"
#  Connected

	   if flash: GPIO.output(7, GPIO.HIGH)
	   if networkerror !=0 :
               logging.debug('Network Up')
               networkerror = 0
	   flash = False
	   sleeptime = 1
	else:
#	   print "No connection"
#  NOT connected
	   flash = True
	   sleeptime = 0.2

# Delete Restart signal files
# Only enable if file successfully deleted & detection doesn't cause error
# This should avoid errorneous deadly reboot loop!
try: os.remove('/var/www/restart.force-shutdown')
except: pass
try: os.remove('/var/www/restart.force-restart')
except: pass
try: os.remove('/var/www/restart.network-restart')
except: pass
try: os.remove('/var/www/restart.shairport-restart')
except: pass
try: os.remove('/var/www/restart.raspotify-restart')
except: pass
try: os.remove('/var/www/restart.daapd-restart')
except: pass
restartenabled = (not os.path.isfile('/var/www/restart.force-shutdown') and 
                  not os.path.isfile('/var/www/restart.force-restart') and
                  not os.path.isfile('/var/www/restart.network-restart') and
                  not os.path.isfile('/var/www/restart.shairport-restart') and
                  not os.path.isfile('/var/www/restart.daapd-restart'))

# Force /var/log permissions
# os.system("sudo chmod 777 /var/log")
# Create force disk check file
# os.system("sudo touch /forcefsck")

# Now we are programming pin 31 as an interrupt input
# it will react on a falling edge and call our interrupt routine "Int_shutdown"
#GPIO.add_event_detect(5, GPIO.FALLING, callback = Int_shutdown, bouncetime = 200)   
GPIO.add_event_detect(5, GPIO.FALLING, callback = Int_shutdown)   

# do nothing while waiting for button to be pressed
# unless told to flash (set in shutdown above)
while True:
	if not shutdown: check_network()
	if (not shutdown) and restartenabled: check_restart()

#	if not shutdown: check_logstatus()
#	if GPIO.input(5):
#		print("Input was HIGH")
#	else:
#		print("Input was LOW")
#	print("Pulse",flash,LED,sleeptime)

	if flash:
		LED = not LED
		GPIO.output(7, LED)

	time.sleep(sleeptime)


# never actually reaches here, but included for completeness
#
GPIO.output(7, GPIO.HIGH)
GPIO.cleanup()


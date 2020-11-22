ifeq ($(PREFIX),)
    PREFIX =  /usr/local
endif

DEFINES = $(PLATFORM_DEFINES)

.PHONY:	web

install: web
	install -m 755 -D scripts/spotify_gpio.sh /home/pi
	install -m 644 -D scripts/monitor.py /home/pi
ifeq ($(wildcard /var/www/html/changelog.txt),)
	install -m 644 -D *.php /var/www/html
	install -m 644 -D *.css /var/www/html
	install -m 644 -D *.txt /var/www/html
endif
ifeq ($(wildcard /etc/default/raspotify),)
	install -m 666 -D scripts/raspotify /etc/default/raspotify
endif
ifeq ($(wildcard /lib/systemd/system/raspotify.service),)
	install -m 644 -D scripts/raspotify.service /lib/systemd/system/raspotify.service
endif

clear:
	rm -f /etc/default/raspotify
	rm -f /lib/systemd/system/raspotify.service
	rm -f /var/www/html/changelog.txt
	rm -f /home/pi/spotify_gpio.sh
	rm -f /home/pi/monitor.py
	rm -f /var/log/shairport.log
	rm -f /var/log/forked_daapd.log
	rm -f /var/log/monitor.log

release: clear
	$(MAKE) install


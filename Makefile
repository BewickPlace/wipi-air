ifeq ($(PREFIX),)
    PREFIX =  /usr/local
endif

DEFINES = $(PLATFORM_DEFINES)

.PHONY:	web

install: web
	install -m 755 -D scripts/spotify_gpio.sh /usr/bin
	install -m 644 -D scripts/monitor.py /usr/bin
ifeq ($(wildcard /var/www/html/changelog.txt),)
	install -m 644 -D *.php /var/www/html
	install -m 644 -D *.css /var/www/html
	install -m 644 -D *.txt /var/www/html
endif

clear:
	rm -f /var/www/html/changelog.txt
	rm -f /usr/bin/spotify_gpio.sh
	rm -f /usr/bin/monitor.py
	rm -f /var/log/monitor.log

release: clear
	$(MAKE) install


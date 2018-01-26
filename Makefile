ifeq ($(PREFIX),)
    PREFIX =  /usr/local
endif

DEFINES = $(PLATFORM_DEFINES)

.PHONY:	web

install: web
	install -m 644 -D *.php /var/www/html
	install -m 644 -D *.css /var/www/html
	install -m 644 -D *.txt /var/www/html


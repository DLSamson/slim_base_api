tables:
	php bin/create_tables.php

data:
	php bin/fill_tables.php

database: tables data

docker: database
	php-fpm -R

.PHONY:
	tables data database
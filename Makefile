tables:
	php bin/create_tables.php

database: tables

.PHONY:
	tables data database
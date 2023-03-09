tables:
	php bin/create_tables.php

data:
	php bin/fill_tables.php

database: tables data

.PHONY:
	tables data database
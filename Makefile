tables:
	php bin/create_tables.php

fake_data:
	php bin/fill_tables.php

.PHONY:
	tables docker
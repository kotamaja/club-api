# Unit tests

## database creation

Some unittests require a test database. In order to run those tests, you need to execute followings commands from the root mysql user 

```sql

CREATE DATABASE IF NOT EXISTS clubapi_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON clubapi_test.* TO 'clubapi'@'%';

FLUSH PRIVILEGES;

```


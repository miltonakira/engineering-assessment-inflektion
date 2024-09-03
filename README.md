# peak-one-dev-test
Test applied by Peak One Dev

## Important Links
- [Laravel 11.x Documentation](https://laravel.com/docs/11.x/releases)
- [Composer](https://getcomposer.org/)
- [Postman](https://www.postman.com/)

## Getting Start

### Git 

Cloning the repository with the "git clone" command, remembering that there are PHP dependencies in relation to Laravel 11.x that will be listed below.

```bash
cd <chosen directory>/
git clone https://github.com/miltonakira/engineering-assessment-inflektion.git .
```

#### Git / Server Dependecies

#### PHP Version

| Dependency | Required Version |
|------------|------------------|
| PHP        | 8.2.0 or later   |

#### Required PHP Extensions

| Extension     | Description                               |
|---------------|-------------------------------------------|
| `ext-bcmath`  | Provides support for arbitrary precision mathematics. |
| `ext-curl`    | Allows for URL and file operations via HTTP. |
| `ext-fileinfo`| Provides information about file types.    |
| `ext-json`    | Enables JSON parsing and encoding.        |
| `ext-mbstring`| Supports multi-byte string operations.    |
| `ext-openssl` | Provides encryption and decryption support.|
| `ext-pdo`     | PHP Data Objects, used for database interactions. |
| `ext-tokenizer` | Supports PHP tokenization for parsing.   |
| `ext-xml`     | Provides XML parsing and manipulation.    |

#### Database Drivers

| Driver          | Description                         |
|-----------------|-------------------------------------|
| `ext-pdo_mysql` | PDO driver for MySQL.                |
| `ext-pdo_pgsql` | PDO driver for PostgreSQL.           |
| `ext-pdo_sqlite`| PDO driver for SQLite.               |

#### Composer Dependencies

| Package                     | Description                                      |
|-----------------------------|--------------------------------------------------|
| `illuminate/support`        | Provides various support classes for Laravel.   |
| `illuminate/database`       | Provides the database layer for Laravel.        |
| `illuminate/filesystem`     | Provides file handling support.                 |
| `illuminate/queue`          | Provides queue management.                      |
| `illuminate/mail`           | Provides mail functionality.                    |
| `illuminate/validation`     | Provides validation support.                    |
| `illuminate/auth`           | Provides authentication support.                |
| `illuminate/session`        | Provides session management.                    |
| `illuminate/broadcasting`   | Provides broadcasting support.                  |
| `illuminate/cache`          | Provides caching support.                       |
| `illuminate/notifications`  | Provides notification support.                  |
| `laravel/framework`         | The core Laravel framework package.             |
| `guzzlehttp/guzzle`         | A PHP HTTP client for sending HTTP requests.    |

#### Development Dependencies

| Package                        | Description                                      |
|--------------------------------|--------------------------------------------------|
| `phpunit/phpunit`              | Testing framework for PHP.                      |
| `mockery/mockery`              | Mocking library for PHP.                        |
| `nunomaduro/collision`         | Provides error handling and formatting for the CLI. |

## Configurations

Once it has started, you need to configure the MySql Database.
To do this, just change the .env file in the container located at "/var/www/html/test/.env".
Change the values ​​of environment variables with the prefix "DB_". 

There are some additional steps for the project that runs the service locally, it will be necessary to install composer and perform an update.
(instructions here https://getcomposer.org/download/)

```bash
vi <chosen directory>/.env

# Windows
php composer.phar update 

# Linux/Unix
composer update
```

As there is a user/authentication table for API access, it will be necessary to run the "php artisan migrate" command inside the container.

In local server:
```bash
cd <chosen directory>
php artisan migrate
```

## Testing on Postman

To make it easier to view the API, I left the Postman import file in the project root: https://github.com/miltonakira/peak-one-dev-test/PEAK_ONE_DEV-TEST.postman_collection.json.
Import it and change the URL variable to the location where you chose to host the file (including the port number if is not 80).

The first step is to create a user with the "REGISTER" method. After registering the user, it will be possible to use the "LOGIN" method to generate the access token for the methods created to manipulate emails.


## Parsing a raw_data from a register

A schedule was configured to run every 1 hour for records that have "processing" as the raw_data value. Since the field cannot be null, I added this flag when including the record, however, in the case of inclusion, the record is already parsing the email body.
However, you can force it manually by running the command:

```bash
php artisan emails:process
```

Or a specific record by passing its ID

```bash
php artisan emails:process <ID>
```

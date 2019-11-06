# CuxFramework

This is a PHP 7 framework for speeding up the development process.
It provides a list of basic tools, moving the focus from implementation ( PHP-wise ) to development ( logic-wise):

* MVC
* Database Wrapper ( MySQL, over PDO )
* Error/Exception handling
* Sessions management
* Translations management
* Caching system
* Logging system
* URL/routes management
* Traffic logging component
* Basic RBAC system

The project actually started as a college task and it outgrew it's initial purpose.

In order to get started using the framework, you'll need to setup your environment:

## Step 1
**Install the project as a composer package**

```bash
$ mkdir cuxframework-demo
$ cd cuxframework-demo
$ mkdir components config console controllers css forms models modules
$ touch composer.json index.php .htaccess config/config.php config/db.php
$ touch index.php
$ touch .htaccess
```

Edit the following files:

* `composer.json`
```json
{
    "name": "mihaicux/cuxframework-demo",
    "type": "project",
    "description": "Simple PHP Framework",
    "author": "Mihail Cuculici <mihail.cuculici@gmail.com>",
    "autoload": {
        "psr-4": {
            "modules\\": "modules/",
            "controllers\\": "controllers/",
            "models\\": "models/",
            "forms\\": "forms/",
            "components\\": "components/",
            "console\\": "console/",
            "Box\\Spout\\": "vendor/spout/src/Spout"
        }
    },
    "require-dev": {
        "phpunit/phpunit": "^7"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mihaicux2/cuxframework.git"
        }
    ],
    "config": {
        "github-oauth": { 
            "github.com": "##############################3"
        }
    },
    "require": {
        "mihaicux/cuxframework": "dev-master"
    }
}
```

* index.php
```php
<?php
include("vendor/autoload.php");

use CuxFramework\utils\Cux;

$config = require_once('config/config.php');

Cux::config($config);

Cux::getInstance()->run();
```

* config/config.php
```php
<?php

use CuxFramework\components\log\CuxLogger;
use CuxFramework\utils\Cux;

// app config
return array(
    "version" => "0.9.4",
    "appName" => "Cux Forum",
    "debug" => false,
    "language" => "en",
    "components" => array(
        "logger" => array(
            "class" => 'CuxFramework\components\log\CuxDBLogger',
            "params" => array(
                "logLevel" => CuxLogger::EMERGENCY + CuxLogger::ALERT +CuxLogger::CRITICAL + CuxLogger::ERROR
            )
        ),
        "db" => require(__DIR__ . '/db.php'),
        "cache" => array(
            'class' => 'CuxFramework\components\cache\CuxMemCache',
            'params' => array(
                "key" => "zX12d45Fd#^",
                "keyPrefix" => "cache_",
                "servers" => array(
                    array(
                       "host" => "127.0.0.1",
                        "port" => "11211"
                    )
                )
            )
        ),
        "session" => array(
            "class" => "CuxFramework\components\session\CuxCachedSession",
            "params" => array(
                "key" => "#DE4123Lgds",
                "keyPrefix" => "session_",
                "lifeTime" => 3600,
                "restoreFromCookie" => true,
                "sessionName" => "CuxAppSession",
                "httpOnly" => true,
                "secureCookie" => false
            )
        ),
    )
);
```

* config/db.php
```php
<?php
// if you're not using the framework under docker, you should really change this to a predefined string
$host = getenv("MYSQL_HOST");
$username = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");

return array(
    'class' => 'CuxFramework\components\db\PDOWrapper',
    'params' => array(
        'connectionString' => "mysql:host={$host};dbname=optional",
        'username' => "{$username}",
        'password' => "{$password}",
        'fetchMode' => PDO::FETCH_ASSOC,
        'errorMode' => PDO::ERRMODE_EXCEPTION,
        'enableSchemaCache' => true,
        'schemaCacheTimeout' => 3600,
    )
);
```

* .htaccess
```bash
RewriteEngine On

# Put your installation directory here:
# If your URL is www.example.com/, use /
# If your URL is http://localhost/begin/, use /begin/

RewriteBase /

# Do not enable rewriting for files or directories that exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# For reuests that are not actual files or directories,
# Rewrite to index.php/URL
RewriteRule ^(.*)$ index.php/$1 [PT,L]

```

After you're done editing the files, you can install the repositoty

```bash
$ composer install
```

Your directory structure should look like this:
```
project
|   .htaccess
│   composer.json   
│   index.php      
└───config
│   │   config.php
│   │   db.php
│   
└───vendor
|   └───mihaicux
|       |    └───cuxframework
|       |    |   |   .git
|       |    |   |   .composer.json
|       |    |   └───src
|       |    |   |   └───CuxFramework
|       |    |   |   |   |     components
|       |    |   |   |   |     ...
```




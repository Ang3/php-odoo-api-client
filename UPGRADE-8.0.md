UPGRADE FROM 7.x to 8.0
=======================

**Binary compatibility break (BC break)**

Global
------

- The PHP extension ```php-xmlrpc``` is not used anymore.
- The PHP extension ```php-json``` is now required.
- DBAL features was moved to the package [ang3/php-odoo-dbal](...)
  - You must install it to be able to use query features.

Client
------

- Renamed method ```Client::create()``` to ```Client::insert()```
- Renamed static method ```Client::createFromConfig()``` to ```Client::create()```

Expression builder
------------------

- New folder/namespace architecture.
- Domain expressions fixes.
UPGRADE FROM 7.x to 8.0
=======================

**Binary compatibility break (BC break)**

Global
------

- The PHP extension ```php-xmlrpc``` is not used anymore.
- The PHP extension ```php-json``` is now required.
- Github workflows

Client
------

- Renamed method ```Client::create()``` to ```Client::insert()```
- Renamed static method ```Client::createFromConfig()``` to ```Client::create()```
- All methods fixed and tested.

Expression builder
------------------

Architecture update:
  - Domain classes moved from `Ang3\Component\Odoo\DBAL\Expression` to `Ang3\Component\Odoo\DBAL\Expression\Domain`
  - Operation classes moved from `Ang3\Component\Odoo\DBAL\Expression` to `Ang3\Component\Odoo\DBAL\Expression\Operation`
- Domain expressions fixes.
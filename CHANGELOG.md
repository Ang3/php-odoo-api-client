CHANGELOG
=========

7.0
---

* Marked client ORM built-in methods as deprecated (no BC)
* Added record manager that allows to manage models
* Added record repository that allows to execute and isolate queries for a dedicated model.
* Added query builder and ORM query that allows to create queries easily in OOP context.
* Added objects and iterable support for the expression builder.
* Added record manager schema to get model names and metadata.
* Fixed method ```count()```and added method ```countAll()``` 
  ([Issue 8](#https://github.com/Ang3/php-odoo-api-client/issues/8)).

6.1
===

- Replaced package [darkaonline/ripcord](https://packagist.org/packages/DarkaOnLine/Ripcord) by
  [ang3/php-xmlrpc-client](https://packagist.org/packages/ang3/php-xmlrpc-client).
- Implemented interface ```Ang3\Component\Odoo\Exception\ExceptionInterface``` for all client exceptions.
- Fixed methods ```read()``` for integers or arrays ([Issue 6](https://github.com/Ang3/php-odoo-api-client/issues/6)).
- Fixed methods when argument ```$criteria``` can be NULL
- Fixed logging.
- Deleted useless files and updated ```.gitignore```

6.0
===

- Removed dependency of package [ang3/php-dev-binaries](https://packagist.org/packages/ang3/php-dev-binaries).
- Added methods ```searchOne``` and ```searchAll```.
- Back to package [darkaonline/ripcord](https://packagist.org/packages/DarkaOnLine/Ripcord).
- Removed XML-RPC client.
- Removed remote exception.
- Removed trace back feature.
Setup Test DB Command for Laravel
====
[![Latest Stable Version](https://poser.pugx.org/socialengine/setup-test-db/version.png)](https://packagist.org/packages/socialengine/setup-test-db) [![License](https://poser.pugx.org/socialengine/setup-test-db/license.svg)](https://packagist.org/packages/socialengine/setup-test-db)

Integration tests in laravel are great, but the common way to maintain the database is a giant time sink
of re-seeding and re-migrating for every single test.

This command and bootstrap file aims to remove the needless reseeding and migrating (since you're using
transactions anyways, right?) for every test and instead gives your tests a "clean" migrated and seeded db.

Works with `sqlite` and any others supported by Eloquent.

**This automatically truncates the database, so be careful**

## Installation

Require this package in composer:
```
$ composer require socialengine/setup-test-db
```

After updating composer, add the ServiceProvider to the providers array in `app/config/app.php`.

```
'SocialEngine\TestDbSetup\ServiceProvider',
```

Add a `bootstrap/testing.php` or copy from `vendor/socialengine/setup-test-db/bootstrap/testing.php`

```php
<?php

// bootstrap/testing.php
$testEnv = (getenv('APP_ENV')) ? : 'testing';

passthru("php " . __DIR__ . "/../artisan db:seed-test --env={$testEnv}");

require __DIR__ . '/autoload.php';
```

**Note: it truncates non-sqlite db, so be careful, watch the env setting and adjust your database.php config
accordingly**. You can also turn off truncation in the config.

Change your `phpunit` (or any other framework) bootstrap file from `bootstrap/autoload.php` to `bootstrap/testing.php`:
```xml
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="./bootstrap/testing.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false"
>
```

Remove all the migration stuff from your `TestCase.php`

Finally, run your tests in 1/3 the time they used to.

You can also publish the config-file to change seeder class used.

```
$ php artisan config:publish socialengine/setup-test-db
```

## Further reading and inspiration

Most of this is based on the work outlined by Chris Duell in his
[Speeding up PHP unit tests 15 times](http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/)
blog post:

> An app I’m currently working on has less that 50% code coverage, and the tests took over 35 seconds to complete.
>
> It was enough for me to no longer bother consistently running tests, which almost defeats the purpose of self
> testing code. Until I got fed up enough to spend the time on speeding up the tests,
> **by fifteen times** (and almost halving memory usage).

## License

MIT

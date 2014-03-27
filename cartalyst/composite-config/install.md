# Installation

Installation is as easy as adding the following to your composer.json:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://packages.cartalyst.com"
        }
    ],
    "require": {
        "cartalyst/composite-config": "1.0.*"
    }
}
```


#### Laravel 4

Add `Cartalyst\CompositeConfig\CompositeConfigServiceProvider` to your service provider array.

Usage is identical to [that explained in the Laravel documentation](http://laravel.com/docs/configuration#introduction) - `Config::get('key')`.

##### Limitations

In Laravel 4, configuration is used to resolve database credentials as well as a number of core options. Because of this, any config items requested before the composite config package is loaded will be cached. Typically, this is just the config within `app/config/app.php` and `app/config/database.php` and `app/config/session.php`. There is a way around this if you require to override these config items:

```php
Config::set('*::app', null);
Config::set('*::database', null);
Config::set('*::session', null);
```

This will remove these items from the cache and force them to be re-fetched from the database. Be sure to inject the new values into anywhere they've been previously injected!

> **Note:** Most people shouldn't need to worry about the above.


#### Everywhere else

Setting up the composite config loader is identical to setting

```php
$loader = new Cartalyst\CompositeConfig\CompositeLoader(new Illuminate\Filesystem\Filesystem, '/path/to/config/files');

// Attach the optional database loading functionality. Without this, the
// composite loader acts like the default file loader.
$database = new Illuminate\Database\Connection(new PDO('mysql:dbname=my_database;host=127.0.0.1'), $prefix = '');
$loader->setDatabase($database);

$config = new Illuminate\Config\Repository($loader);
```

### Saving Config

There are two ways of saving configuration items.

1. Runtime
2. Persisting

#### Runtime

Saving configuration at runtime is as easy as calling `$config->set($key, $value);` (or `Config::set($key, $value)` in Laravel 4). There's nothing more to it - during that request, calling `$config->get($key)` will return whatever you assigned `$value` as.

#### Persisting

Persisting configuration is also easy, it just requires calling `set()` on the loader instead of the repository:

```php
$config->getLoader()->set('foo', 'bar');

// Now, every request from here on will respect the value you provided.
echo $config->get('foo'); // Echoes "bar"
```

^**Note:** When persisting a config item, the value will be (by default) persisted for the current environment only. So, if you're running in the "local" environment and switch to "production", your item won't load. Overcoming this is easy, just provide "*" as the third parameter - `$config->getLoader()->set('foo', 'bar', '*')` and it will work for all environments!

### Cascading

Below is the order in which items are cascaded:

1. Database configuration for the current environment
2. Database configuration for all environments (persisted by providing "*" as the third parameter)
3. Filesystem configuration for the current environment
4. Filesystem configuration for all environmentts

Any number of these may be absent, it will be skipped.

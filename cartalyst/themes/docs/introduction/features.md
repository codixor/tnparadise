## Features {#features}

Our themes package enhances any application while allowing the freedom to organize and maintain your application views as you see fit. Themes currently supports the following feature set.

* Any number of theme locations.
* Support for theme "areas" (such as "backend" or "frontend" themes, you can choose anything).
* Unlimited theme inheritence; you can make an unlimited chain of themes which inherit off another theme. Views and assets cascade throughout theme inheritence.
* Fallback theme support; nominate a theme which views and assets fallback to if they cannot be found in the active theme hierarchy.
* Asset compilation (LESS, SASS, SCSS, CoffeeScript etc), minification and compression into one asset (configurable per environment).
* Powerful, dynamically generated static asset cache; assets are cached when compiled to static files, which is blazingly fast. We **don't** serve assets through frameworks/controllers as this adds significant overhead.
* Theme publishing (publish your own packages / extensions with support for any theme in them and publish them from within the [Artisan CLI in Laravel 4](http://four.laravel.com/docs/artisan)). Of course, this can work outside of Laravel as well.

## Configuring Themes

Configuring themes is relatively straight-forward in Laravel 4. Simply run the following command to publish the configuration files to your application:

	php artisan config:publish cartalyst/themes

Now, visit `app/config/packages/cartalyst/themes/config.php` and setup the configuration as required.

There are a couple of things you should be aware of with themes configuration:

1. Once you publish themes, you will need to configure the theme paths as they were relative to the config file before it was published.
2. Assets are compiled to a directory relative to your public directory. By defaults this is `public/assets/cache` (where `public` is the public directory). Please ensure this folder exists and is writeable by your web server.
3. By default we include a number of filters which are applied to assets with a given file extension. The packages are not required to make themes run and therefore are not installed, bug *suggested*. See [Installing](/themes/installation/laravel-4) for a reference for the packages you should install to work with the default filters. Of course, feel free to change or remove these, we just think they're good defaults.

### Selecting the active theme

---

Selecting the active theme is very easy. Themes have slugs which identify them and by default the slug should match the path to the theme. How does this work?

Let's say your themes are located under `public/themes`. If you wanted a theme with a slug `foo/bar`, it should reside under `public/themes/foo/bar`. A theme with a slug `default` should reside under `public/themes/default`. It's that simple.

We have added the concept of theme "areas". Say you were building an eCommerce website, you may wish to have themes for the "frontend" of your website as well as the "backend".

How do we do this? It's actually pretty much the same. Let's say we have a theme with `backend::foo/bar`. It should be under `public/themes/backend/foo/bar`. Think of areas as namespacing.

To set the active theme, ensure it's folder structure exists as well as a `theme.json` file (see below) and just put it's slug as the `active` theme in your configuration.

Setting the fallback theme is the exact same process except you are entering the `fallback` theme in your configuration.

### theme.json

---

Each theme should have a `theme.json` file in it. It should be the following structure:

	{
		"slug": "frontend::foo/bar",
		"parent": "frontend::base",
		"name": "Foo-Bar theme",
		"author": "Cartalyst LLC",
		"description": "The best theme you've ever seen.",
		"version": "1.0.0"
	}

Your `theme.json` contains the following attributes:

1. **Slug** - the slug of the theme. It should match the folder structure the theme resides in and is used to validate the theme package has found the right theme.
2. **Parent** - the slug of the parent of this theme. Any views / assets which cannot be found in this theme will cascade to that parent theme. *Not required.*
3. **Name** - A human friendly name of the theme. *Not required.*
4. **Author** - The author of the theme (person, nickname, company etc). *Not required.*
5. **Description** - A description of the theme. *Not required.*
6. **Version** - The theme's version. *Not required.*

### Debug

---

There is a "debug" option in your configuration. When themes is in debug mode, assets are compiled (as per the selected filters) however are kept separate. When debug mode is turned off, all style assets are compiled into one file as well as all script assets. This greatly reduces the number of HTTP requests made to the server by users and speeds up the application.

This value is set to `null` at first, meaning if your application is running in the `production` environment, it will automatically turn debug off. You can explicitly set it to `true` or `false`.

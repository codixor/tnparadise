## Publishing Themes

When we outlined our [philosophy]({url}/introduction/concepts) for creating themes, you may have noticed that we believe in a separation of themes from backend code.

This is all "good and well", but what happens if you want to distribute a package or [extension](http://cartalyst.com/arsenal) with theme support in it? An example of this is if somebody releases an extension for our Cartalyst eCommerce Platform (in the works) and wants to include files for the default "Cartalyst eCommerce" themes within the extension.

The answer?

**Theme publishing**.

We have included a `ThemePublisher` class which allows you to publish the theme files from a given source to any valid themes. It's automatic and it just works.

All you have to do is include a `themes/` folder inside a composer packge or Cartalyst Extension, and the correct folder structure and valid folders will be copied across.

Let's say we have the following themes in our application:

1. `backend::default`
2. `frontend::default`

If we have a package with views and assets for this theme, we could have the following file structure:

	themes
	|  backend
	|  |  default
	|  |  |  assets
	|  |  |  |  css
	|  |  |  |  |  foo.css
	|  |  |  packages
	|  |  |  |  packagename
	|  |  |  |  |  views
	|  |  |  |  |  |  register.blade.php

You get the point. Just include the path to the theme (with the theme's area if it has one) and the files in the correct structure. Then, call `ThemePublisher::publishPackage('packagename');` or `ThemePublisher::publishExtension($extension);`.

We've integrated the theme publisher with the Artisan CLI:

	php artisan theme:publish --package=foo/bar
	php artisan theme:publish --extension=baz/bat
	php artisan theme:publish --extensions

Enjoy!

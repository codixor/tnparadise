## Usage

Ensure you have an instance of Cartalyst\Themes\Assets\AssetManager setup. If you've followed our [Laravel 4 installation](/themes/installation/laravel-4) guide, this is already done for you and is accessible through the static `Asset` methods. We'll be using this facade in this article to keep the syntax cleaner.

### Queuing assets

---

Queuing assets is very easy in our Themes package:

	// Queue a CSS asset called "style".
	Asset::queue('style', 'css/style.css');

	// Queue a LESS asset called "custom", which relies
	// on "style" being loaded first
	Asset::queue('custom', 'namespace::css/custom.less', 'style');

	// Queue jQuery
	Asset::queue('jquery', 'js/jquery-1.9.1.min.js');

	// And bootstrap
	Asset::queue('bootstrap', 'vendor/package::js/bootstrap.js', 'jquery');

	// And our custom JS
	Asset::queue('script', 'js/script.js', array('bootstrap', 'jquery'));

As you can see, the arguments are:

1. **Alias** - This is a friendly name of the asset. For example, 'jquery'. It may refer to a physical JavaScript file with a version. When you refer to it however, you can just use 'jquery'. If the path of the physical file changes, nothing needs to change except for where you register the style.
2. **Path** - This is the path to the asset. This is resolved to the `assets` folder in a theme or a theme's `namespaces` or `packages` path. This works automatically.
3. **Dependencies** - This is a string or array of aliases for the dependencies for this asset. This may be required if you are loading assets in partial views, as some rendering engines (such as Blade) will load the partial views before the main view, meaning assets are not queued in the order you expect. Rule of thumb, supply dependencies and everything will work beautifully!

#### Compiling assets

Compiling assets is even simpler than queuing them:

	$urls = Asset::compileStyles();
	$urls = Asset::compileScripts();

In the code above, `$urls` will be an array of URLs to the compiled assets. If you are in `debug` mode, this array will only have 1 entry for styles, one for scripts. If your'e not, you will have multiple entries. Iterating through these urls is the best way to output them:

	foreach (Asset::compiledStyles() as $url) echo $url;

Of course, all of the above code can be adapted for Laravel's Blade templating engine:

	{{ Asset::queue('style', 'css/style.css') }}

	@foreach (Asset::getCompiledStyles() as $style)
		<link href="{{ $style }}" rel="stylesheet">
	@endforeach

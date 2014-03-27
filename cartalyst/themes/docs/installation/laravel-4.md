## Installing in Laravel 4 (with Composer)

Installing on Laravel 4 is quite simple, it only requires three simple steps to install Themes:

### Step 1 {#step-1}

---

Ensure your `composer.json` file has the following structure (that you have the `repositories` and the `require` entry):

	{
		"repositories": [
			{
				"type": "composer",
				"url": "http://packages.cartalyst.com"
			}
		],
		"require": {
			"cartalyst/themes": "2.0.*"
		}
	}

You may need to add a `"minimum-stability": "dev"` flag if it doesn't already exist until `cartalyst/themes` has been marked as stable. A default Laravel 4 installation has this already as Laravel 4 isn't marked as stable yet.

Assets are powered by [Assetic](#). Assetic has a brilliant filters system which allow you to manipulate assets before outputting them (compile LESS/SCSS or minify CSS/JS as a couple of examples). We don't require you use any of these filters, so we have no strict dependencies for them. However, for an "out of the box" solution, you should also add the following suggested dependencies to your root `composer.json` file:

	"require": {
		"cartalyst/assetic-filters": "1.0.*",
		"jasonlewis/basset": "dev-master",
		"leafo/lessphp": "dev-master",
		"leafo/scssphp": "dev-master",
		"leafo/scssphp-compass": "dev-master",
		"lmammino/jsmin4assetic": "dev-master",
		"natxet/CssMin": "dev-master"
	}

### Step 2 {#step-2}

---

Add `Cartalyst\Themes\ThemeServiceProvider` to the list of service providers in `app/config/app.php`

### Step 3 {#step-3}  *(optional)*

---

Append the following to the list of class aliases in `app/config/app.php`:

	'Theme' => 'Cartalyst\Themes\Facades\Theme',
	'Asset' => 'Cartalyst\Themes\Facades\Asset',

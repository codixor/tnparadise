## Installing Everywhere Else

### Step 1

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
			"cartalyst/sentry-social": "3.0.*"
		}
	}

### Step 2

---

Create an instance of Sentry Social:

	$manager = new Cartalyst\SentrySocial\Manager($instanceOfSentry);
	
	// In FuelPHP / CodeIgniter
	$manager = new Cartalyst\SentrySocial\Manager(Sentry::instance());

This instance will need to be shared / passed around, until we provide Facades for these frameworks (which will be coming in the **very** short-term).

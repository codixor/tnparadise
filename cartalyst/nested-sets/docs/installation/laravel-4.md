# Install & Configure in Laravel 4

## Composer {#composer}

Open your `composer.json` file and add the following lines:

	{
		"require": {
			"cartalyst/nested-sets": "2.0.*"
		},
		"repositories": [
			{
				"type": "composer",
				"url": "http://packages.cartalyst.com"
			}
		],
		"minimum-stability": "stable"
	}

Run composer update from the command line

	composer update

## Service Provider {#service-provider}

Add the following to the list of service providers in `app/config/app.php`.

	'Cartalyst\NestedSets\NestedSetsServiceProvider',

## Create a new Migration

Run the following command `php artisan migrate:make create_menus_table`

Open the `app/database/migration/xxxx_xx_xxxxxx_create_menus_table.php` file

> Note: the `xxxx_xx_xxxxxx` is your current date and you can customize the migration name to fit your own needs.

Inside the `up()` method add the following:

```
Schema::create('menus', function($table)
{
	$table->increments('id');

	// You can rename these columns to whatever you want, just remember
	// to update them on the $reservedAttributes inside your model.
	$table->integer('lft');
	$table->integer('rgt');
	$table->integer('menu');

	// you can add your own fields here

	$table->timestamps();
	$table->engine = 'InnoDB';
	$table->unique(array('lft', 'rgt', 'menu'));
});
```

> Note: For more information about the Schema Builder, check the [Laravel docs](http://laravel.com/docs/schema)

Once your migration is finished, you just need to run `php artisan migrate`.

## Create a new Model

Here is a default Eloquent model that you can use

```
use Cartalyst\NestedSets\Nodes\EloquentNode;

class Menu extends EloquentNode {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = array('lft', 'rgt', 'menu', 'depth', 'created_at', 'updated_at');

    /**
     * Array of attributes reserved for the worker. These attributes
     * cannot be set publically, only internally and shouldn't
     * really be set outside this class.
     *
     * @var array
     */
    protected $reservedAttributes = array(
        'left'  => 'lft',
        'right' => 'rgt',
        'tree'  => 'tree',
    );

}
```

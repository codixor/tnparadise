# Usage

## Extend your Class {#extend-class}

Your Eloquent implementation must extend `Cartalyst\Attributes\Entity`

	use Cartalyst\Attributes\Entity;

	class Page extends Entity {

		protected $guarded = array(
			'id',
			'created_at',
			'updated_at',
		);

	}

> **Note:** You cannot use the $fillable property, you must use the $guarded property.

## Create an Attribute {#create-attribute}

	use Cartalyst\Attributes\Attribute

	Attribute::create(
		array(
			'slug' => 'meta_title',
		),
	);


## Assign Values to Attributes {#assign-values}

To set values for the new attribute you simply provide the attribute slug as a parameter to the create or update function

	// Create a new record
	$page = Page::create(
		array(
			'name' => 'Page #1',
			'slug' => 'page-1',
			'meta_title' => 'Homepage', // An Attribute
		),
	);

	// Update an existing record
	$page = Page::find(1);

	$data = array(
		'options' => array( // An Attribute
			'option1',
			'option2',
		),
	);

	$page->fill($data)->update();

> **Note:** You can also set the attribute value to an array, it will be encoded as json.

## Retrieve Values {#retrieve-values}

Values are retrieved along with your object

	$page = Page::find(1);

Retrieved Object

	// Page Object

	{
		"id":2,
		"name": "Page #1",
		"slug": "page-1",
		"meta_title":"The Default Homepage",
		"values":[
			{
				"id":1,
				"attribute_id":1,
				"entity_type":"Foo\Bar\User",
				"entity_id":2,
				"value":"The Default Homepage",
				"created_at":"2013-12-12 18:26:17",
				"updated_at":"2013-12-12 18:26:17",
				"attribute":{
					"id":1,
					"slug":"meta_title",
					"created_at":"2013-12-11 21:39:32",
					"updated_at":"2013-12-11 21:39:32"
				}
			}
		]
	}

> **Note:** If you use arrays as values, you may want to define an accessor for your attribute to decode json encoded arrays for you

	public function getMetaTitleAttribute($value)
	{
		return is_array(json_decode($value, true)) ? json_decode($value, true) : $value;
	}

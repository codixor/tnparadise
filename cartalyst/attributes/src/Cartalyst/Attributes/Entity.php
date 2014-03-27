<?php namespace Cartalyst\Attributes;
/**
 * Part of the Attributes package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Attributes
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Attributes\Relations\EavValues;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

abstract class Entity extends Model {

	/**
	 * The name of the relationship to the "value" model.
	 *
	 * @var string
	 */
	protected $valueRelation = 'values';

	/**
	 * Holds all the cached attributes.
	 *
	 * @var array
	 */
	protected static $attributesCache = array();

	/**
	 * EAV entity values relationship.
	 *
	 * @return \Cartalyst\Attributes\Value
	 */
	public function values()
	{
		return $this->hasValues('Cartalyst\Attributes\Value', 'entity');
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(array $options = array())
	{
		// Firstly, we will grab all of the extract all EAV attributes out
		// of our attributes array. We will use these after the initial
		// save has taken place
		$eavAttributes = $this->extractEavAttributes();

		// We are wrapping our queries inside a transaction so that protected
		// methods can be accessed, rather than passing a colsure through to
		// the database connection. Once we no longer support PHP 5.3, we
		// can tidy this up a fair chunk.
		$connection = $this->getConnection();
		$connection->beginTransaction();

		// Run queries inside a transaction so any failed EAV queries can
		// rollback the whole process
		try
		{
			// We will now take note if the model exists. We approach adding
			// EAV values differently for new models
			$exists = $this->exists;

			$result = parent::save($options);

			if ($result === false)
			{
				$connection->rollBack();
				return false;
			}

			$eavResult = $this->saveEav($eavAttributes, $exists);

			if ($eavResult === false)
			{
				$connection->rollBack();
				return false;
			}

			// Restore the EAV attributes back to the original array
			$this->restoreEavAttributes($eavAttributes);

			// Everything went well? Tell PDO to commit our queries!
			$connection->commit();
		}
		catch (Exception $e)
		{
			$connection->rollBack();

			throw $e;
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete()
	{
		if ($this->exists)
		{
			$connection = $this->getConnection();
			$connection->beginTransaction();

			// Run queries inside a transaction so any failed EAV queries can
			// rollback the whole process
			try
			{
				$result = parent::delete();

				if ($result === false)
				{
					$connection->rollBack();
					return false;
				}

				$eavResult = $this->deleteEav();

				if ($eavResult === false)
				{
					$connection->rollBack();
					return false;
				}

				// Everything went well? Tell PDO to commit our queries!
				$connection->commit();
			}
			catch (Exception $e)
			{
				$connection->rollBack();

				throw $e;
			}
		}

		return $result;
	}

	/**
	 * Saves EAV attributes to the database. Because this will take place
	 * after a save, a model always "exists". We take an additional flag
	 * to whether the model existed before this first save.
	 *
	 * If it didn't, attaching values can take place more efficiently with
	 * the assumption that there are no attributes for non-existent
	 * models.
	 *
	 * @param  array  $attributes
	 * @param  bool   $exists
	 * @return bool
	 */
	protected function saveEav(array $attributes, $exists)
	{
		if ( ! count($attributes))
		{
			return true;
		}

		// If it already exists, it means we have already loaded up our
		// associated EAV "values" and "attributes". We will spin
		// through "values" and update them where possible.
		// If they don't exist, we will create a new "value".
		if ($exists)
		{
			foreach ($attributes as $key => $value)
			{
				if ($valueInstance = $this->findValue($key))
				{
					if ($this->updateEavValue($valueInstance, $value) === false)
					{
						return false;
					}
				}
				elseif ($this->createEavValue($key, $value) === false)
				{
					return false;
				}
			}
		}

		// If we didn't exist, we will need to create "value" objects
		// for every attribute value. We will then attach them
		// to our relationship manually, so as to save extra
		// queries when we will know the outcome.
		else
		{
			foreach ($attributes as $key => $value)
			{
				$valueInstance = $this->createEavValue($key, $value);

				if ($valueInstance === false)
				{
					return false;
				}
			}

			$relation = $this->getValueRelation();
			$this->setRelation($relation, $this->$relation()->get());
		}

		return true;
	}

	/**
	 * Creates an EAV "value" object with the given key and value.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return \Cartalyst\Attributes\Value|bool
	 */
	protected function createEavValue($key, $value)
	{
		// Grab the attribute instance
		$attributeInstance = $this->findAttribute($key);

		// Grab a new value instance and set it's "value"
		$valueInstance = $this->newValueModel();
		$valueInstance->setValueKey($value);

		// Link it with the attribute
		$relation = $attributeInstance->{$attributeInstance->getValueRelation()}();
		$valueInstance->setAttribute($relation->getPlainForeignKey(), $attributeInstance->getKey());

		// Link it with this entity
		$relation = $this->{$this->getValueRelation()}();
		$valueInstance->setAttribute($relation->getPlainForeignKey(), $this->getKey());
		$valueInstance->setAttribute($relation->getPlainMorphType(), $relation->getMorphClass());

		return $valueInstance->save() ? $valueInstance : false;
	}

	/**
	 * Updates an EAV "value" object with the given value.
	 *
	 * @param  \Cartalyst\Attributes\Value  $valueInstance
	 * @param  mixed  $value
	 * @return \Cartalyst\Attributes\Value|bool
	 */
	protected function updateEavValue(Value $valueInstance, $value)
	{
		$valueInstance->setValueKey($value);

		return $valueInstance->save() ? $valueInstance : false;
	}

	/**
	 * Extracts EAV attributes from the original array of attributes.
	 *
	 * On new models it will return all EAV attributes. On existing
	 * models it will only return dirty attributes as those are
	 * the only ones which have changed and which will need
	 * to be updated.
	 *
	 * @return array
	 */
	protected function extractEavAttributes()
	{
		if ($this->exists)
		{
			$dirty = $this->getDirty();

			if (count($dirty) > 0)
			{
				return $this->pluckEavAttributes($dirty);
			}
		}
		else
		{
			return $this->pluckEavAttributes($this->attributes);
		}

		return array();
	}

	/**
	 * Takes the given array of attributes, compares them to available
	 * attributes and returns the available ones.
	 *
	 * Also plucks any attributes passed from the original
	 * array of the model's attributes.
	 *
	 * @param  array  $attributes
	 * @return array
	 */
	protected function pluckEavAttributes(array $attributes)
	{
		$eavAttributes = array();

		foreach ($attributes as $key => $value)
		{
			$matching = $this->findAttribute($key);

			if ($matching)
			{
				if (is_array($value))
				{
					$value = json_encode($value);
				}

				$eavAttributes[$key] = $value;

				unset($this->attributes[$key]);
			}
		}

		return $eavAttributes;
	}

	/**
	 * Restores EAV attributes back into the main array.
	 *
	 * @param  array  $eavAttributes
	 * @return void
	 */
	protected function restoreEavAttributes(array $eavAttributes)
	{
		$this->attributes = array_merge($this->attributes, $eavAttributes);
	}

	/**
	 * Deletes all "value" data associated with the entity.
	 *
	 * @return bool
	 */
	protected function deleteEav()
	{
		$relation = $this->getValueRelation();

		foreach ($this->$relation as $value)
		{
			if ( ! $value->delete())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get a new query builder for the model's table.
	 *
	 * @param  bool  $excludeDeleted
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newQuery($excludeDeleted = true)
	{
		// We will create a nested relation that's dynamically generated so that all
		// attributes and values are eagerly loaded with models in new queries.
		$withRelation = $this->valueRelation.'.'.$this->newValueModel()->getAttributeRelation();
		$this->with = array_merge($this->with, array($withRelation));

		return parent::newQuery($excludeDeleted);
	}

	/**
	 * Define an EAV value which belongs has values.
	 *
	 * @param  string  $related
	 * @param  string  $name
	 * @param  string  $type
	 * @param  string  $id
	 * @param  string  $localKey
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function hasValues($related, $name, $type = null, $id = null, $localKey = null)
	{
		$instance = new $related;

		list($type, $id) = $this->getMorphs($name, $type, $id);

		$table = $instance->getTable();

		$localKey = $localKey ?: $this->getKeyName();

		return new EavValues($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
	}

	/**
	 * Get the "value" relation name.
	 *
	 * @return string
	 */
	public function getValueRelation()
	{
		return $this->valueRelation;
	}

	/**
	 * Returns a new instance of a "value" model.
	 *
	 * @return \Cartalyst\Attributes\Value
	 */
	public function newValueModel()
	{
		$relation = $this->valueRelation;

		return $this->$relation()->getRelated();
	}

	/**
	 * Returns a new instance of an "attribute" model.
	 *
	 * @return \Cartalyst\Attributes\Value
	 */
	public function newAttributeModel()
	{
		return $this->newValueModel()->newAttributeModel();
	}

	/**
	 * Find a matching attribute instance for the given key.
	 *
	 * @param  string  $key
	 * @return \Cartalyst\Attributes\Attribute
	 */
	public function findAttribute($key)
	{
		$availableAttributes = static::availableAttributes();

		return array_first($availableAttributes, function($index, $attribute) use ($key)
		{
			return ($attribute->getAttributeKey() == $key);
		});
	}

	/**
	 * Find's an attribute "value" object with the given attribute "key".
	 *
	 * @param  string  $key
	 * @return \Cartalyst\Attributes\Value
	 */
	public function findValue($key)
	{
		if ( ! isset($this->relations[$this->getValueRelation()]))
		{
			throw new RuntimeException("Implement lazy loading attribute values, here and in the EavValues relationship!");
		}

		return array_first($this->{$this->getValueRelation()}, function($index, $value) use ($key)
		{
			return ($value->{$value->getAttributeRelation()}->getAttributeKey() == $key);
		});
	}

	/**
	 * Caches all the available attributes, irrespective of model
	 * instance (for speed).
	 *
	 * @return array
	 */
	public static function availableAttributes()
	{
		$cacheKey = get_called_class();

		if ( ! isset(static::$attributesCache[$cacheKey]))
		{
			static::$attributesCache[$cacheKey] = with(new static)->newAttributeModel()->get();
		}

		return static::$attributesCache[$cacheKey];
	}

	/**
	 * Clears the cache of the available attributes.
	 *
	 * @return void
	 */
	public static function clearAttributesCache()
	{
		$cacheKey = get_called_class();

		if (isset(static::$attributesCache[$cacheKey]))
		{
			unset(static::$attributesCache[$cacheKey]);
		}
	}

}

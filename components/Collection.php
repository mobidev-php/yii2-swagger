<?php

namespace mobidev\swagger\components;

use mobidev\swagger\components\EntityObject;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /** @var Object[] */
    protected $items = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param string $offset
     * @return null|mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param \mobidev\swagger\components\EntityObject[] $objects
     * @return $this
     */
    public function addArray($objects)
    {
        foreach ($objects as $object) {
            $this->add($object);
        }
        return $this;
    }

    /**
     * @param \mobidev\swagger\components\EntityObject $object
     * @return $this
     */
    public function add(EntityObject $object)
    {
        $this->items[$object->id] = $object;
        return $this;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function buildId($value)
    {
        return md5(serialize($value));
    }

    public function last()
    {
        return end($this->items);
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[$item->name] = $item->toArray();
        }
        return $result;
    }

    public function toString()
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[] = $item->__toString();
        }
        return $result;
    }

    /**
     * @param \mobidev\swagger\components\EntityObject $object
     */
    public function delete(EntityObject $object)
    {
        unset($this->items[$object->id]);
    }

    /**
     * find items with selected field and value
     * @param string $field
     * @param string $searchString
     * @return array
     */
    public function find($field, $searchString)
    {
        $items = array_filter($this->items, function($item) use ($field, $searchString) {
            return $item->$field === $searchString;
        });
        return $items;
    }

    /**
     * Fill selected field for all collection
     * @param string $field
     * @param string $value
     * @param array $excludeValue
     */
    public function set($field, $value, array $excludeValue = [])
    {
        foreach ($this->items as $item) {
            if($item->in == 'path'){
                continue;
            }
            if (in_array($item->$field, $excludeValue)) {
                continue;
            }
            $item->required = true;
            $item->$field = $value;
        }
    }

}
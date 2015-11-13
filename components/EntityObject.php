<?php

namespace mobidev\swagger\components;

abstract class EntityObject
{
    /** @var string */
    protected $id;

    /**
     * @param string $name
     * @return mixed
     * @throws \LogicException
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \LogicException("Wrong property $name");
        }

        if ($name == 'id' && empty($this->id)) {
            $this->generateId();
        }
        return $this->$name;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        foreach ($data as $k => $v) {
            if (!property_exists($this, $k)) {
                continue;
            }
            $this->$k = $v;
        }
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws \LogicException
     */
    public function __set($name, $value)
    {
        if (!isset($this->$name)) {
            throw new \LogicException("Wrong property $name");
        }
        $this->$name = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = get_object_vars($this);
        unset($array['id']);
        foreach ($array as $k => $v) {
            if ($v === null) {
                unset($array[$k]);
            }
        }
        return $array;
    }

    /**
     * Generates id for object. It needs for identification objects in the collections
     */
    abstract protected function generateId();
}
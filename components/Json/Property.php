<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\EntityObject;
use yii\base\ErrorException;

class Property extends EntityObject
{
    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /**
     * {@inheritdoc}
     */
    protected function generateId()
    {
        if (empty($this->name)) {
            throw new ErrorException("Property name should be filled");
        }
        $this->id = md5($this->name);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        unset($array['name']);
        return $array;
    }
}

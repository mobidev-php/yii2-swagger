<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\EntityObject;
use yii\base\ErrorException;

class Tag extends EntityObject
{
    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /**
     * {@inheritdoc}
     */
    protected function generateId()
    {
        if (empty($this->name)) {
            throw new ErrorException("Tag name should be filled");
        }
        $this->id = md5($this->name);
    }

    public function __toString()
    {
        return $this->name;
    }
}

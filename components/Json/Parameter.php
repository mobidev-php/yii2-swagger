<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\Object;
use yii\base\ErrorException;

class Parameter extends Object
{
    public $name;
    public $in;
    public $description;
    public $required;
    public $type;
    public $items;
    public $collectionFormat;
    public $schema;

    /**
     * @param string $ref
     */
    public function setSchema($ref)
    {
        $this->schema = ['$ref' => '#/definitions/' . $ref];
    }

    /**
     * @param Definition $def
     */
    public function buildForDefinition($def)
    {
        $this->name = 'body';
        $this->in = 'body';
        $this->required = true;
        $this->setSchema($def->name);
    }

    /**
     * {@inheritdoc}
     * @throws ErrorException
     */
    protected function generateId()
    {
        if (empty($this->name)) {
            throw new ErrorException("Parameter name should be filled");
        }
        $this->id = md5($this->name);
    }

}
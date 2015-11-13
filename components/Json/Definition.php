<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\Collection;
use mobidev\swagger\components\EntityObject;
use yii\rest\Action;

class Definition extends EntityObject
{
    /** @var string */
    public $name;

    /** @var string  */
    public $type = 'object';

    /**
     * @var Collection
     */
    public $properties;

    /**
     * @var Action
     */
    private $action;

    /**
     * @param Action $action
     */
    public function __construct($action)
    {
        $this->action = $action;
        $this->properties = new Collection();
        $this->name = str_replace(['-', '_'], '', ucfirst($this->action->controller->id) . ucfirst($this->action->id . 'Request'));
    }

    /**
     * @param Parameter $parameter
     */
    public function addPropertyFromParameter($parameter)
    {
        $property = new Property();
        $property->name = $parameter->name;
        $property->type =  $parameter->type;
        $this->properties->add($property);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateId()
    {
        $this->id = md5($this->name);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['properties'] = $this->properties->toArray();
        unset($array['name']);
        if (empty($array['properties'])) {
            $array['properties'] = new \stdClass();
        }
        return $array;
    }
}
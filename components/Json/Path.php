<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\Collection;
use mobidev\swagger\components\Object;
use yii\rest\Action;

class Path extends Object
{
    /** @var string */
    public $name;

    /** @var Collection */
    public $verbs;

    /** @var Action */
    private $action;

    /**
     * @param Action $action
     */
    public function __construct($action)
    {
        $this->action = $action;
        $this->name = $this->getPathForAction($action);
        $this->verbs = new Collection();
    }

    /**
     * @param Action $action
     * @return string
     */
    private function getPathForAction($action)
    {
        $path = '/' . strtolower($action->controller->id) . '/' . $action->id;
        if ($action instanceof Action) {
            $path = '/' . strtolower($action->controller->id);
        }
        return $path;
    }

    /**
     * @param string $verbName
     * @param Tag $tag
     */
    public function addVerb($verbName, $tag)
    {
        $in = \Yii::$app->controller->module->defaultInput;
        $verb = new Verb($verbName, $this->action, $in);
        $verb->tags->add($tag);
        $this->verbs->add($verb);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->verbs->toArray();
    }

    /**
     * {@inheritdoc}
     */
    protected function generateId()
    {
        $this->id = md5($this->name);
    }
}
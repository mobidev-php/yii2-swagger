<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\ActionAdapter;
use mobidev\swagger\components\Collection;
use mobidev\swagger\components\Object;
use yii\rest\Action;

class Path extends Object
{
    /** @var string */
    public $name;

    /** @var string */
    public $path;

    /** @var Collection */
    public $verbs;

    /** @var ActionAdapter */
    private $action;

    /**
     * @param Action $action
     */
    public function __construct($action)
    {
        $this->action = $action;
        $this->verbs = new Collection();
        $this->calcNameAndPathForAction();
    }

    private function calcNameAndPathForAction()
    {
        $path = '/' . strtolower($this->action->controller->id) . '/' . $this->action->id;
        $this->name = $path;

        // exclusions for ActiveController actions
        if ($this->action->isActiveAction()) {
            $path = '/' . strtolower($this->action->controller->id);
            if (in_array($this->action->id, ['view', 'delete', 'update'])) {
                $path .= '/{id}';
            }
        }
        $this->path = $path;
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
        $this->id = md5($this->name . $this->path);
    }
}
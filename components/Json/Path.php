<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\ActionAdapter;
use mobidev\swagger\components\Collection;
use mobidev\swagger\components\EntityObject;
use yii\rest\Action;

class Path extends EntityObject
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
        if($this->action->hasMethod('run')){
            $path = $this->getPathFromStandaloneAction();
        }
        $this->path = $path;
    }

    private function getPathFromStandaloneAction()
    {
        $path = '/' . strtolower($this->action->controller->id).'/'.$this->action->id;
        $reflector = new \ReflectionClass($this->action->className());
        $reflectionParameters = $reflector->getMethod('run')->getParameters();
        foreach($reflectionParameters as $reflectionParameter){
            $path .= '/{'.$reflectionParameter->name.'}';
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
        $this->id = md5($this->name . $this->path);
    }
}
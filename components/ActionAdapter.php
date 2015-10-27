<?php

namespace mobidev\swagger\components;

use yii\base\Action;
use yii\base\Model;
use Zend\Code\Reflection\DocBlockReflection;

class ActionAdapter
{
    /** @var Action */
    protected $action;

    /** @var DocBlockReflection */
    private $docBlock;

    /**
     * @param Action $action
     * @param DocBlockReflection|null $docBlock
     */
    public function __construct($action, $docBlock = null)
    {
        $this->action = $action;
        $this->docBlock = $docBlock;
    }

    /**
     * @return array
     */
    public function rules()
    {
        if (method_exists($this->action, 'rules')) {
            return $this->action->rules();
        }
        if ($this->docBlock) {
            return $this->createRulesFromPhpdoc();
        }
        return [];
    }

    /**
     * @return string
     */
    public function getScenario()
    {
        return isset($this->action->scenario) ? $this->action->scenario : Model::SCENARIO_DEFAULT;
    }

    /**
     * @return array
     */
    private function createRulesFromPhpdoc()
    {
        $rules = [];
        // process phpdoc form
        $tags = $this->docBlock->getTags('form');
        if ($tags) {
            $formClass = $tags[0]->getContent();
            $rules = (new $formClass)->rules();
        }
        // process phpdoc params
        $tags = $this->docBlock->getTags('param');
        foreach ($tags as $tag) {
            $rules[] = [
                trim($tag->getVariableName(), '$'),
                $tag->getTypes()[0],
                'query'
            ];
        }
        return $rules;
    }

    /**
     * Returns description for method
     * @return string
     */
    public function description()
    {
        if (method_exists($this->action, 'description')) {
            return $this->action->description();
        }
        return  $this->docBlock ? $this->docBlock->getShortDescription() : '';
    }

    /**
     * Getter
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return $this->action->$property;
    }

    /**
     * @return bool
     */
    public function isActiveAction()
    {
        return $this->action instanceof \yii\rest\Action;
    }

}
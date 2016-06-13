<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\Collection;
use mobidev\swagger\components\EntityObject;
use mobidev\swagger\Module;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\rest\Action;
use yii\rest\ActiveController;
use Yii;

class Verb extends EntityObject
{
    /** @var string */
    public $name;

    /** @var string */
    public $in;

    /** @var string */
    public $description;

    /** @var array */
    public $consumes = [
        "application/json",
    ];

    /** @var array */
    public $produces = [
        "application/json",
    ];

    /** @var array */
    public $responses = [
        '200' => [
            "description" => "Ok",
        ]
    ];

    /** @var Collection */
    public $tags;

    /** @var Collection */
    public $parameters;

    /** @var bool */
    protected $isMultipleParameter = false;

    /** @var Action */
    private $action;

    /** @var Module  */
    private $module;

    /**
     * @param string $name
     * @param Action $action
     * @param string $in
     */
    public function __construct($name, $action, $in)
    {
        $this->module = Yii::$app->controller->module;
        $this->name = $name;
        $this->action = $action;
        $this->in = $in;
        $this->description = $this->getDescriptionForAction();
        $this->responses = $this->getResponsesForAction();
        $this->parameters = new Collection();
        $this->tags = new Collection();
        $this->buildParametersFromRules();
        $this->buildDefinitions();
    }

    /**
     * @return bool
     */
    protected function isMultipleParameter()
    {
        $res = $this->isMultipleParameter;
        $this->isMultipleParameter = false;
        return $res;
    }

    /**
     * @return string
     */
    private function getDescriptionForAction()
    {
        if (!method_exists($this->action, 'description')) {
            return '';
        }
        return $this->action->description();
    }

    /**
     * @return string
     */
    private function getResponsesForAction()
    {
        $responses = $this->action->responses();
        if($responses){
            return $responses;
        }else{
            return $this->responses;
        }
    }

    /**
     * Create request parameters from model rules
     */
    private function buildParametersFromRules()
    {
        // on GET-request we can to send only query string parameters
        if ($this->name == 'get') {
            $this->in = 'query';
        }

        // additional parameters from config, usually HTTP-headers
        if ($this->module->additionalFields) {
            foreach ($this->module->additionalFields as $field) {
                $param = new Parameter();
                $param->setData($field);
                $this->parameters->add($param);
            }
        }

        $rules = $this->getRulesForAction();
        if (!is_array($rules)) {
            return;
        }

        $parameters = [];
        if ($this->action->hasMethod('run')) {
            $reflector = new \ReflectionClass($this->action->className());
            $reflectionParameters = $reflector->getMethod('run')->getParameters();
            foreach($reflectionParameters as $reflectionParameter){
                $parameters[$reflectionParameter->name] = [
                    'name' => $reflectionParameter->name,
                    'in' => 'path',
                    'description' => $reflectionParameter->name,
                    'type' => 'string',
                ];
                if(!$reflectionParameter->isOptional()){
                    $parameters[$reflectionParameter->name]['required'] = true;
                }
            }
        }

        foreach ($rules as $rule) {
            $rule[0] = is_array($rule[0]) ? $rule[0] : [$rule[0]];
            foreach ($rule[0] as $field) {
                if (!array_key_exists($field, $parameters)) {
                    $parameters[$field] = [
                        'name' => $field,
                        'in' => isset($rule[2]) ? $rule[2] : $this->in,
                        'description' => $field,
                        'type' => 'string',
                    ];
                }
                if ($rule[1] instanceof \Closure) {
                    $method = 'ruleString';
                } else {
                    $method = 'rule' . ucfirst($rule[1]);
                }
                if (method_exists($this, $method)) {
                    $res = call_user_func([$this, $method], $rule);
                    $res['isMultiple'] = $this->isMultipleParameter();
                    $parameters[$field] = ArrayHelper::merge($parameters[$field], $res);
                }
            }
        }
        $parameters = array_values($parameters);
        foreach ($parameters as $param) {
            $p = new Parameter();
            $p->setData($param);
            $this->parameters->add($p);
        }

        if ($this->parameters->find('type', 'file')) {
            $this->in = 'formData';
            $this->consumes = ['multipart/form-data'];
            $this->parameters->set('in', 'formData', ['header']);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'description' => $this->description,
            'parameters' => array_values($this->parameters->toArray()),
            'tags' => $this->tags->toString(),
            'consumes' => $this->consumes,
            'produces' => $this->produces,
            'responses' => $this->responses,
        ];
    }


    /**
     * @return array
     */
    private function getRulesForAction()
    {
        if ($this->action->controller instanceof ActiveController) {
            return $this->getRulesForActiveAction($this->action);
        }
        $scenario = $this->action->getScenario();
        $rules = $this->action->rules();
        $model = (new $this->action->modelClass(['scenario' => $scenario]));
        if (!($model instanceof DynamicModel)) {
            $scenarioAttributes = isset($model->scenarios()[$scenario]) ? $model->scenarios()[$scenario] : [];
            foreach ($rules as $key => $rule) {
                if (is_array($rule[0])) {
                    $rules[$key][0] = array_intersect($rule[0], $scenarioAttributes);
                    if (empty($rules[$key][0])) {
                        unset($rules[$key]);
                    }
                }
                if (is_string($rule[0]) && !(in_array($rule[0], $scenarioAttributes))) {
                    unset($rules[$key]);
                }
            }
        }
        return $rules;
    }

    /**
     * @param Action $action
     * @return string
     */
    private function getRulesForActiveAction($action)
    {
        $rules = [];
        if (in_array($action->id, ['create', 'update'])) {
            $model = $action->controller->modelClass;
            /** @var Model $model */
            $rules = (new $model())->rules();
        }

        if (in_array($action->id, ['view', 'delete', 'update'])) {
            $rules[] = ['id', 'integer', 'path'];
        }
        return $rules;
    }

    /**
     * Create definitions if need
     * @throws \yii\base\InvalidConfigException
     */
    private function buildDefinitions()
    {
        if ($this->in != 'body') {
            return;
        }
        $def = new Definition($this->action);

        // get parameters and make of them a properties for definition
        foreach ($this->parameters as $id => $parameter) {
            /** @var  Parameter $parameter  */
            if ($parameter->in != 'body') {
                continue;
            }
            $this->parameters->delete($parameter);
            $def->addPropertyFromParameter($parameter);
        }
        Yii::$app->get('doc')->definitions->add($def);

        // we must create parameter with Schema for definition
        $parameter = new Parameter();
        $parameter->buildForDefinition($def);
        $this->parameters->add($parameter);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateId()
    {
        $this->id = md5($this->name);
    }

    /**
     * Callback function for @buildParametersFromRules
     * @return array
     */
    protected function ruleRequired()
    {
        return ['required' => true];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @return array
     */
    protected function ruleEmail()
    {
        return ['type' => 'string'];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @return array
     */
    protected function ruleInteger()
    {
        return ['type' => 'integer'];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @return array
     */
    protected function ruleNumeric()
    {
        return ['type' => 'float'];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @return array
     */
    protected function ruleBoolean()
    {
        return ['type' => 'integer'];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @param array $rule
     * @return array
     */
    protected function ruleEach($rule)
    {
        if ($this->module->defaultInput == 'body') {
            return ['type' => 'string'];
        }

        $ret = [
            'type' => 'array',
            'items' => [
                'type' => 'string',
            ],
            'paramType' => 'form',
            'collectionFormat' => 'brackets',
        ];
        $method = 'rule' . ucfirst($rule['rule'][0]);
        if (method_exists($this, $method)) {
            $res = call_user_func([$this, $method], $rule);
            $ret['items'] = $res;
        }
        return $ret;
    }

    /**
     * Callback function for @buildParametersFromRules
     * @param array $rule
     * @return array
     */
    protected function ruleFile($rule)
    {
        if (isset($rule['maxFiles']) &&  $rule['maxFiles'] > 1) {
            $this->isMultipleParameter = true;
        }
        return ['type' => 'file'];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @param array $rule
     * @return array
     */
    protected function ruleDefault($rule)
    {
        return ['default' => $rule['value']];
    }

    /**
     * Callback function for @buildParametersFromRules
     * @param array $rule
     * @return array|null
     */
    protected function ruleString($rule)
    {
        $ret = [];
        $map = [
            'max' => 'maxLength',
            'min' => 'minLength',
        ];
        foreach ($map as $key => $value) {
            if (array_key_exists($key, $rule)) {
                $ret[$value] = $rule[$key];
            }
        }
        return $ret;
    }

}
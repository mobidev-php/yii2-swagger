<?php

namespace mobidev\swagger\components\api;

use yii\base\Exception;
use yii\base\Model;
use yii\base\DynamicModel;
use Yii;
use yii\helpers\Json;

class Action extends \yii\base\Action
{
    /* @var $model Model */
    public $model;

    /** @var string */
    public $modelClass = 'yii\base\DynamicModel';

    /** @var string  */
    public $scenario = Model::SCENARIO_DEFAULT;

    public $errors = [];

    /**
     * You must override this method in your Action for adding
     * description to swagger autodocumentation
     * @return string
     */
    public function description()
    {
        return '';
    }

    /**
     * Returns attributes for Dynamic Model
     * @return array
     * @throws Exception
     */
    protected function dynamicModelAttributes()
    {
        $attributes = [];
        foreach ($this->rules() as $rule) {
            $ruleAttrs = is_array($rule[0]) ? $rule[0] : [$rule[0]];
            $attributes = array_merge($attributes, $ruleAttrs);
        }
        return array_unique($attributes);
    }

    /**
     * You must override this method in oun Action if used DynamicModel
     * @return array
     * @throws Exception
     */
    public function rules()
    {
        if ($this->modelClass == 'yii\base\DynamicModel') {
            throw new Exception('You must redefine rules() method in your Action if you use DynamicModel as modelClass');
        }
        if ($this->createModel()) {
            return $this->model->rules();
        }
        return [];
    }

    /**
     * Create form model for handling request
     * @return DynamicModel|Model
     * @throws Exception
     */
    private function createModel()
    {
        if ($this->model) {
            return $this->model;
        }
        if (!$this->modelClass) {
            throw new Exception("modelClass should be defined");
        }

        if ($this->modelClass == 'yii\base\DynamicModel') {
            $attributes = $this->dynamicModelAttributes();
            if (empty($attributes)) {
                throw new Exception("You must specify attributes if you use DynamicModel as an Action model");
            }
            $this->model = new DynamicModel($attributes);
            foreach ($this->rules() as $rule) {
                $attributes = isset($rule[0]) ? $rule[0] : null;
                $validator = isset($rule[1]) ? $rule[1] : null;
                $options = $rule;
                unset($options[0]);
                unset($options[1]);
                $this->model->addRule($attributes, $validator, $options);
            }
        } else {
            $this->model = new $this->modelClass();
        }
        $this->model->scenario = $this->scenario;
        return $this->model;
    }

    /**
     * @inheritdoc
     *
     * @param array $params
     * @return mixed|\yii\web\Response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function runWithParams($params)
    {
        if (!$this->createModel()) {
            return parent::runWithParams($params);
        }
        if ($this->loadAndValidateRequest()) {
            return parent::runWithParams($params);
        }
        throw new DataValidationHttpException($this->model->getErrors());
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    private function loadAndValidateRequest()
    {
        $this->model->load(Yii::$app->request->get(), '');
        $this->model->load(Json::decode(Yii::$app->request->getRawBody()), '');
        $this->loadFiles();
        $this->model->validate();
        return !$this->model->hasErrors();
    }

    private function loadFiles()
    {
        foreach($_FILES as $field => $FILE){
            if($this->model->hasProperty($field)){
                $uploadedFile = \yii\web\UploadedFile::getInstanceByName($field);
                $this->model->{$field} = $uploadedFile;
            }
        }
    }

}
<?php

namespace mobidev\swagger;

use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\console\Application;
use Yii;

class Module extends BaseModule implements BootstrapInterface
{

    public $jsonPath = '@api/web/swagger.json';
    public $controllerPath = '@api/modules/v1/controllers';
    public $host = 'your-domain-name.com';
    public $basePath = '/v1';
    public $apiVersion = '1.0.0';
    public $schemes = ["http", "https"];
    public $consumes = ["application/json"];
    public $produces = ["application/json"];
    public $description = "API documentation (swagger-2.0 specification)";
    public $defaultInput = "formData";
    public $additionalFields = [];

    public function init()
    {
        $this->controllerNamespace =  __NAMESPACE__ .'\commands';
        Yii::setAlias('@mobidev', str_replace('\\', '/', $this->controllerNamespace));
        parent::init();
    }

    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace =  __NAMESPACE__ .'\commands';
            Yii::setAlias('@mobidev', str_replace('\\', '/', $this->controllerNamespace));
        }
    }


}

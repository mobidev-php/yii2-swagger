<?php

namespace mobidev\swagger\commands;

use mobidev\swagger\components\ActionAdapter;
use mobidev\swagger\components\Json\Document;
use mobidev\swagger\components\Json\Tag;
use mobidev\swagger\Module;
use yii\console\Controller;
use Yii;
use yii\helpers\FileHelper;
use yii\web\User;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * Generates API in swagger format
 */
class GenerateController extends Controller
{
    /**
     * @var Module $module the module that this controller belongs to.
     */
    public $module;

    /**
     * Run a generation of swagger json-document
     * @throws \yii\base\InvalidConfigException
     */
    public function actionJson()
    {
        // workaround for identity absence in console application
        Yii::$container->set('user', ['class' => User::className(),'identityClass' => 'mobidev\swagger\MockUser']);

        // Document generation
        $doc = new Document();
        Yii::$app->set('doc', $doc);
        $controllers = $this->getControllers();
        /** @var \yii\base\Controller $controller */
        foreach ($controllers as $key => $controller) {
            $tag = new Tag();
            $tag->name = $key;
            $doc->addTag($tag);
            $actions = $this->getActions($controller);
            foreach ($actions as $actionName => $action) {
                $doc->handleAction($action);
            }
        }
        $doc->generateFile(Yii::getAlias($this->module->jsonPath));
        $this->stdout('Done' . PHP_EOL);
    }

    /**
     * Returns API controllers
     * @return array|\yii\base\Controller[]
     */
    protected function getControllers()
    {
        $controllers = [];
        $controllerPath = \Yii::getAlias($this->module->controllerPath);
        $controllerPaths = FileHelper::findFiles($controllerPath, ['only' => ['*Controller.php']]);
        foreach ($controllerPaths as $path) {
            $tmp = explode(DIRECTORY_SEPARATOR, $path);
            $filename = end($tmp);
            $class = substr($filename, 0, -4);
            $id = substr($class, 0, -10);
            $class = $this->getNameSpaceFromFile($path) . '\\' . $class;
            $object = new $class($id, \Yii::$app->module);
            $controllers[$id] = $object;
        }
        return $controllers;
    }

    /**
     * Returns namespace from php-file
     * @param string $filename
     * @return string|null
     */
    private function getNameSpaceFromFile($filename)
    {
        $src = file_get_contents($filename);
        if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * @param \yii\base\Controller $controller
     * @return array
     */
    protected function getActions(\yii\base\Controller $controller)
    {
        $actions = [];
        // inline actions
        $reflection = new \ReflectionObject($controller);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = array_filter($methods, function ($method) {
            return strpos($method->name, 'action') === 0 && $method->name != 'actions';
        });
        foreach ($methods as $method) {
            $actionId = strtolower(preg_replace('/([A-Z]){1}/', '-$1', lcfirst(substr($method->name, 6))));
            $dockBlock = null;
            try {
                $dockBlock = new DocBlockReflection($method);
            } catch (\Exception $e) {
            }
            $action = new ActionAdapter($controller->createAction($actionId), $dockBlock);
            $actions[$actionId] = $action;
        }
        // external actions
        foreach ($controller->actions() as $actionId => $alias) {
            $actions[$actionId] = new ActionAdapter($controller->createAction($actionId));
        }
        return $actions;
    }

}

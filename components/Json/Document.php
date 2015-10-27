<?php

namespace mobidev\swagger\components\Json;

use mobidev\swagger\components\Collection;
use mobidev\swagger\components\PathCollection;
use mobidev\swagger\Module;
use yii\rest\Action;
use yii\rest\ActiveController;
use Yii;

class Document
{
    /** @var Collection */
    private $tags;

    /** @var Collection */
    private $paths;

    /** @var Collection */
    public $definitions;

    /** @var Module */
    private $module;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->tags = new Collection();
        $this->paths = new PathCollection();
        $this->definitions = new Collection();
        $this->module = Yii::$app->controller->module;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function addTag($tag)
    {
        $this->tags->add($tag);
        return $this;
    }

    /**
     * @param Action $action
     */
    public function handleAction($action)
    {
        $lastTag = $this->tags->last();
        $path = new Path($action);
        $verbs = $this->getVerbsForAction($action);
        foreach ($verbs as $verb) {
            $path->addVerb($verb, $lastTag);
        }
        $this->paths->add($path);
    }

    /**
     * @param string $filename
     */
    public function generateFile($filename)
    {
        $fh = fopen($filename, 'w');
        fwrite($fh, json_encode($this->getDocument()));
        fclose($fh);
    }

    /**
     * @return array
     */
    public function getDocument()
    {
        return [
            "swagger" => "2.0",
            "info" => [
                "version" => $this->module->apiVersion,
                "title" => Yii::$app->name,
                "description" => $this->module->description,
//                "termsOfService" => "http =>//swagger.io/terms/",
//                "contact" => [
//                    "name" => "Admin"
//                ],
//                "license" => [
//                    "name" => "My license"
//                ]
            ],
            "host" => $this->module->host,
            "basePath" => $this->module->basePath,
            "schemes" => $this->module->schemes,
            "consumes" => $this->module->consumes,
            "produces" => $this->module->produces,
            'tags' => array_values($this->tags->toArray()),
            'paths' => $this->paths->toArray(),
            'definitions' => $this->definitions->toArray(),
        ];
    }

    /**
     * @param Action $action
     * @return array
     */
    private function getVerbsForAction($action)
    {
        if ($action->controller instanceof ActiveController) {
            // exclude Options action
            if ($action->id == 'options') {
                return [];
            }
            if (isset($action->controller->behaviors['verbFilter']->actions[$action->id])) {
                $verbs = $action->controller->behaviors['verbFilter']->actions[$action->id];
                $verbs = array_map('strtolower', $verbs);
                // filter unsupported requests
                $verbs = array_filter($verbs, function ($value) {
                    return !in_array($value, ['options', 'head', 'patch']);
                });
                return $verbs;
            }
        }

        if (!array_key_exists('verbFilter', $action->controller->behaviors)) {
            return ['get'];
        }
        $verbs = array_filter($action->controller->behaviors['verbFilter']->actions[$action->id], function($value) {
            return $value != 'options';
        });
        return $verbs;
    }
}
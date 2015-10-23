Swagger Extension
=================
Extension give ability to generate API documentation in swagger format

Installation
------------

1. Add package repository to composer.json:
```
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "mobidev/yii2-swagger",
                "version": "dev-master",
                "source": {
                    "url": "git@gitlab.mobidev.biz:web/yii2-swagger.git",
                    "type": "git",
                    "reference": "master"
                }
            }
        }
    ],
```
2. Add autoload to composer.json:
```
    "autoload": {
        "psr-4": {
            "mobidev\\swagger\\": "vendor/mobidev/yii2-swagger"
        }
    },
```
3. Run command:
```
composer require "mobidev/yii2-swagger" "dev-master"
```

Usage
-----
1. Add module settings to console config:
```php
return [
    'bootstrap' => ['gii', 'swagger'],
    'modules' => [
        'gii' => 'yii\gii\Module',
        'swagger' => [
            'class' => 'mobidev\swagger\Module',
            'jsonPath' => '@api/web/swagger-dev.json',
            'host' => 'api.192.168.33.68.xip.io',
            'basePath' => '/v1',
            'description' => 'My Project API documentation (swagger-2.0 specification)',
            'defaultInput' => 'body',
        ],
    ],
];
```
2. Run command for generating json document:
```
./yii swagger/generate/json
```
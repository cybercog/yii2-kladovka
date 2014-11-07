<?php

namespace yii\kladovka;

use yii\base\Module as YiiModule,
    yii\base\BootstrapInterface,
    yii\web\Application as YiiWebApplication,
    yii\console\Application as YiiConsoleApplication,
    Yii;


class Module extends YiiModule implements BootstrapInterface
{

    public function bootstrap($app)
    {
        if ($app instanceof YiiWebApplication) {
            $app->getI18n()->translations['kladovka'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@yii/kladovka/messages'
            ];
        } elseif (YII_ENV_DEV && ($app instanceof YiiConsoleApplication)) {
            $gii = Yii::$app->getModule('gii');
            if ($gii) {
                $gii->generators = array_merge($gii->generators, [
                    'model2' => 'yii\kladovka\generators\model2\Generator',
                    'search' => 'yii\kladovka\generators\search\Generator',
                    'search2' => 'yii\kladovka\generators\search2\Generator'
                ]);
                $app->controllerMap[$this->id] = 'yii\kladovka\console\GenerateController';
            }
        }
    }
}

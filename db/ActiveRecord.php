<?php

namespace yii\kladovka\db;

use yii\db\ActiveRecord as YiiActiveRecord,
    yii\helpers\VarDumper,
    Yii;


class ActiveRecord extends YiiActiveRecord
{

    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    public static function dump()
    {
        if ($this->hasErrors()) {
            VarDumper::dump([
                'class' => get_class($this),
                'attributes' => $this->getAttributes(),
                'errors' => $this->getErrors()
            ]);
        } else {
            VarDumper::dump([
                'class' => get_class($this),
                'attributes' => $this->getAttributes()
            ]);
        }
    }

    public static function dumpAsString()
    {
        if ($this->hasErrors()) {
            return VarDumper::dumpAsString([
                'class' => get_class($this),
                'attributes' => $this->getAttributes(),
                'errors' => $this->getErrors()
            ]);
        } else {
            return VarDumper::dumpAsString([
                'class' => get_class($this),
                'attributes' => $this->getAttributes()
            ]);
        }
    }

    public static function log($message = 'No message.', $category = 'application')
    {
        if ($this->hasErrors()) {
            Yii::error(VarDumper::dumpAsString([
                'message' => $message,
                'class' => get_class($this),
                'attributes' => $this->getAttributes(),
                'errors' => $this->getErrors()
            ]), $category);
        } else {
            Yii::info(VarDumper::dumpAsString([
                'message' => $message,
                'class' => get_class($this),
                'attributes' => $this->getAttributes()
            ]), $category);
        }
    }
}

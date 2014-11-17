<?php

namespace yii\kladovka\console;

use yii\console\Controller,
    yii\kladovka\helpers\Log,
    yii\helpers\Inflector,
    Yii;


class GenerateController extends Controller
{

    public $dirMode = '0777';

    public $overwriteAll = false;

    public function options($actionId)
    {
        return array_merge(parent::options($actionId), ['dirMode', 'overwriteAll']);
    }

    public function actionDbSchema()
    {
        Log::beginMethod(__METHOD__);
        $sqlPath = Yii::getAlias('@app/sql');
        if (!is_dir($sqlPath)) {
            mkdir($sqlPath, octdec($this->dirMode));
        }
        $db = Yii::$app->getDb();
        parse_str(str_replace(';', '&', substr($db->dsn, 6)), $dsnParams);
        $filename = $sqlPath . DIRECTORY_SEPARATOR . $dsnParams['dbname'] . '-schema.sql';
        $command = 'mysqldump --create-options --no-data --events' .
            ' --host=' . escapeshellarg(array_key_exists('host', $dsnParams) ? $dsnParams['host'] : 'localhost') .
            ' --user=' . escapeshellarg($db->username) .
            ' --password=' . escapeshellarg($db->password) .
            ' ' . escapeshellarg($dsnParams['dbname']) .
            ' | sed -e ' . escapeshellarg('s/ AUTO_INCREMENT=[0-9]\+//') .
            ' > ' . escapeshellarg($filename);
        passthru($command);
        Log::endMethod(__METHOD__);
    }

    public function actionBaseModels()
    {
        Log::beginMethod(__METHOD__);
        $readonlyPath = Yii::getAlias('@app/models/readonly');
        if (!is_dir($readonlyPath)) {
            mkdir($readonlyPath, octdec($this->dirMode));
        }
        $baseClass = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\db\ActiveRecord' : 'yii\kladovka\db\ActiveRecord';
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $modelClass = Inflector::classify($tableName) . 'Base';
            $args = [
                'tableName' => $tableName,
                'ns' => $ns,
                'modelClass' => $modelClass,
                'baseClass' => $baseClass,
                'generateLabelsFromComments' => 1,
                'interactive' => 0,
                'overwrite' => 1
            ];
            $command = getcwd() . '/yii gii/model --' . vsprintf(implode('=%s --', array_keys($args)) . '=%s', array_map('escapeshellarg', array_values($args)));
            passthru($command);
        }
        Log::endMethod(__METHOD__);
    }

    public function actionModels()
    {
        Log::beginMethod(__METHOD__);
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $className = Inflector::classify($tableName);
            $command = getcwd() . '/yii gii/model2' .
                ' --modelClass=' . escapeshellarg($ns . '\\' . $className . 'Base') .
                ' --secondModelClass=' . escapeshellarg($ns . '\\' . $className) .
                ' --interactive=0' .
                ' --overwrite=' . escapeshellarg($this->overwriteAll ? '1' : '0');
            passthru($command);
        }
        Log::endMethod(__METHOD__);
    }

    public function actionBaseSearchModels()
    {
        Log::beginMethod(__METHOD__);
        $searchPath = Yii::getAlias('@app/models/search');
        if (!is_dir($searchPath)) {
            mkdir($searchPath, octdec($this->dirMode));
        }
        $readonlySearchPath = Yii::getAlias('@app/models/readonly/search');
        if (!is_dir($readonlySearchPath)) {
            mkdir($readonlySearchPath, octdec($this->dirMode));
        }
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $className = Inflector::classify($tableName);
            $command = getcwd() . '/yii gii/search' .
                ' --modelClass=' . escapeshellarg($ns . '\\' . $className) .
                ' --searchModelClass=' . escapeshellarg($ns . '\search\\' . $className . 'SearchBase') .
                ' --interactive=0' .
                ' --overwrite=1';
            passthru($command);
        }
        Log::endMethod(__METHOD__);
    }

    public function actionSearchModels()
    {
        Log::beginMethod(__METHOD__);
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly\search' : 'app\models\search';
            $className = Inflector::classify($tableName);
            $command = getcwd() . '/yii gii/search2' .
                ' --modelClass=' . escapeshellarg($ns . '\\' . $className . 'SearchBase') .
                ' --secondModelClass=' . escapeshellarg($ns . '\\' . $className . 'Search') .
                ' --interactive=0' .
                ' --overwrite=' . escapeshellarg($this->overwriteAll ? '1' : '0');
            passthru($command);
        }
        Log::endMethod(__METHOD__);
    }

    public function actionControllers()
    {
        Log::beginMethod(__METHOD__);
        $baseControllerClass = Yii::$app->hasModule('mozayka') ? 'yii\mozayka\crud\ActiveController' : 'yii\web\Controller';
        foreach (Yii::$app->getDb()->createCommand('SHOW FULL TABLES;')->queryAll(\PDO::FETCH_NUM) as $row) {
            list($tableName, $tableType) = $row;
            $ns = ($tableType == 'VIEW') ? 'app\models\readonly' : 'app\models';
            $className = Inflector::classify($tableName);
            $command = getcwd() . '/yii gii/controller2' .
                ' --modelClass=' . escapeshellarg($ns . '\\' . $className) .
                ' --controllerClass=' . escapeshellarg('app\controllers\\' . $className . 'Controller') .
                ' --baseControllerClass=' . escapeshellarg($baseControllerClass) .
                ' --interactive=0' .
                ' --overwrite=' . escapeshellarg($this->overwriteAll ? '1' : '0');
            passthru($command);
        }
        Log::endMethod(__METHOD__);
    }

    public function actionMakeAll()
    {
        $this->actionDbSchema();
        $this->actionBaseModels();
        $this->actionModels();
        $this->actionBaseSearchModels();
        $this->actionSearchModels();
        //$this->actionControllers();
    }

    public function actionIndex()
    {
        passthru(getcwd() . '/yii help ' . $this->id);
    }
}

<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../components/Env.php');
app\components\Env::init();

if ($debug = app\components\Env::get('YII_DEBUG')) {
    define('YII_DEBUG', $debug);
}
if ($env = app\components\Env::get('YII_ENV')) {
    define('YII_ENV', $env);
}

require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();

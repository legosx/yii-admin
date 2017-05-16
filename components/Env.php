<?php
namespace app\components;

use yii;
use Dotenv\Dotenv;

class Env
{
    public static $path = __DIR__ . '/../';

    public static function get($name)
    {
        switch (true) {
            case array_key_exists($name, $_ENV):
                return $_ENV[$name];
            case array_key_exists($name, $_SERVER):
                return $_SERVER[$name];
            default:
                $value = getenv($name);
                return $value === false ? null : $value;
        }
    }

    public static function init()
    {
        $dotenv = new Dotenv(self::$path);
        $dotenv->overload();
    }

}
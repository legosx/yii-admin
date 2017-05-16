<?php

namespace app\components;

use yii;
use MongoDB\BSON\Regex;

class Poster
{
    public static function files()
    {
        /** @var yii\mongodb\Connection $db */
        $db = Yii::$app->db;
        return $db->getFileCollection('poster');
    }

    public static function get($filename)
    {
        if ($document = self::files()->findOne(['filename' => $filename])) {
            return self::files()->createDownload($document)->toString();
        }

        return false;
    }

    public static function save($filename, $content)
    {
        if ($doc = self::files()->findOne(['filename' => $filename])) {
            self::files()->remove(Arr::get($doc, '_id'));
        }

        self::files()->createUpload(['filename' => $filename])->addContent($content)->complete();
    }

    public static function delete($id = null)
    {
        self::files()->remove(array_filter(['filename' => $id ? new Regex('^' . $id . '[\.|_](.*?)[png|jpg|gif]$', 'g') : null]));
    }
}
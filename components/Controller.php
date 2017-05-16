<?php

namespace app\components;

use yii;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDatetime;
use app\models\Movie;
use yii\web\Response;
use yii\web\Controller as YiiController;

class Controller extends YiiController
{
    const ACTION_REMOVE_CATEGORY = 'remove-category';
    const ACTION_TO_CATEGORY = 'to-category';
    const ACTION_UPDATE_CATEGORY = 'update-category';
    const ACTION_OUTPUT = 'output';

    protected function _movieEditableHandle()
    {
        $post = Yii::$app->request->post();

        $attribute = (array)Arr::get($post, 'editableAttribute');
        $key = (array)Arr::get($post, 'editableKey');
        foreach ($key as $key_key => $key_value) {
            $key[$key_key] = new ObjectID($key_value);
        }
        $index = Arr::get($post, 'editableIndex');
        $action = Arr::get($post, 'editableAction');
        $output = Arr::get($post, 'editableOutput');

        if ($output && !in_array($output, ['/common/category', 'grid/actions', 'grid/access'])) {
            $output = null;
        }

        if ($action == self::ACTION_OUTPUT && $output) {
            return $this->renderPartial($output, [
                'data' => Movie::findOne([
                    '_id' => $key
                ])
            ]);
        }

        $update = [];
        $result = [];

        foreach ($attribute as $attr) {
            $value = Arr::get($post, ['Movie', $index, $attr]);
            if ($value === null) {
                $update[$attr] = $value;
                continue;
            }
            $result[] = $value;
            $update[$attr] = $value;
        }

        $update['updated'] = new UTCDatetime(time() * 1000);

        switch ($action) {
            case self::ACTION_UPDATE_CATEGORY:
                $category = (array)Arr::get($update, 'category');
                foreach ($category as $id => $value) {
                    $category[$id] = new ObjectID($value);
                }
                Movie::updateCategory($key[0], $category);
                break;
            case self::ACTION_TO_CATEGORY:
                $category = new ObjectID($update['category']);
                Movie::addToCategory($category, $key);
                break;
            case self::ACTION_REMOVE_CATEGORY:
                Movie::deleteCategory($key);
                break;
            default:
                Movie::updateAll($update, ['_id' => $key]);
                break;
        }

        if ($output) {
            return $this->renderPartial($output, [
                'data' => Movie::findOne([
                    '_id' => $key
                ])
            ]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['output' => count($result) == 1 ? $result[0] : $result, 'message' => ''];
    }
}
<?php

namespace app\models\forms;

use yii;
use yii\base\Model;

class CreateCategoryForm extends Model
{
    public $name;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'required', 'message' => 'Required {attribute}']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Category name'
        ];
    }
}

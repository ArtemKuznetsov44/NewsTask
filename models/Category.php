<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/***
 * @property int $id;
 * @property string $title;
 * @property int $parent_cat_id;
*/

class Category extends ActiveRecord
{

    public static function tableName(): string
    {
        return 'category';
    }

    public function rules(): array
    {
        return [
            ['id', 'integer'],
            ['parent_cat_id', 'integer'],
            ['title', 'required'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Category Id',
            'title' => 'Category Title',
        ];
    }

    public static function getListItems(): array {

        $model_items = Category::find()->asArray()->all();
        $data = [];

        foreach ($model_items as $category) {
            $data[$category['id']] = $category['title'];
        }
        return $data;
    }
}
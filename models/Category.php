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
            ['title', 'required'],
            ['title', 'string', 'min' => 2, 'tooShort' => 'Title is too small!'],
            ['parent_cat_id', 'exist', 'skipOnEmpty' => true, 'targetClass' => Category::class, 'targetAttribute' => ['parent_cat_id' => 'id']]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Идентификатор категории',
            'title' => 'Название категории',
            'parent_cat_id' => "Родительская категория"
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
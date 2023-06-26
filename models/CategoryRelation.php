<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/***
 * @property int $parent_cat_id;
 * @property int $current_cat_id;
*/

class CategoryRelation extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'category_relation';
    }

    public function rules():array {
        return [
            [['parent_cat_id', 'current_cat_id'], 'integer'],
            // Checks that Model Category has such categories identifiers:
            ['parent_cat_id', 'exist', 'skipOnError' => true,  'targetClass' => Category::class, 'targetAttribute' => ['parent_cat_id'=> 'id']],
            ['current_cat_id', 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['current_cat_id' => 'id']]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'parent_cat_id' => 'Parent category Id',
            'current_cat_id' => 'Current category Id'
        ];
    }

    public function getAllCatsFromParentCat(): ActiveQuery {
        return $this->hasMany(Category::class, ['id' => 'parent_cat_id']);
    }
}

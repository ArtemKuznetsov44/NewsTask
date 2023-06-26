<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/***
 * @property int $id;
 * @property string $link_url;
 * @property string $title;
 * @property string $main_content;
 * @property boolean $is_active;
 * @property integer $created_at;
 * @property integer $updated_at;
 * @property int $category_id;
 *
 * @property Category $category;
 * @
 */
class News extends ActiveRecord
{

//    public function behaviors(): array
//    {
//        return [
//            [
//                'class' => TimestampBehavior::class,
//                'createdAtAttribute' => 'created_at',
//                'updatedAtAttribute' => 'updated_at',
//            ]
//        ];
//    }


    public static function tableName(): string
    {
        return 'news';
    }

    public function rules(): array
    {
        return [
            [['id', 'category_id'], 'integer'],
            [['category_id', 'title'], 'required'],
            ['main_content', 'trim'],
            ['link_url', 'match', 'pattern' => '/^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$/i', 'message' => 'Invalid URL format.'],
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'News Id',
            'link_url' => 'Link to news page',
            'title' => 'The title of current news',
            'main_content' => 'Base content of news',
            'is_active' => 'Is Active',
            'created_at' => 'Creation Time',
            'updated_at' => 'Last Modification Time',
            'category_id' => 'Category Id',
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
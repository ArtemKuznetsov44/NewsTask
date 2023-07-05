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
 * @property Comment[] $comments;
 *
 */
class News extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ]
        ];
    }


    public static function tableName(): string
    {
        return 'news';
    }

    public function rules(): array
    {
        return [
            ['category_id', 'required'],
            ['title', 'required'],
            ['main_content', 'trim'],
            ['link_url', 'match', 'pattern' => '/^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$/i', 'message' => 'Invalid URL format.'],
            ['category_id', 'exist', 'skipOnEmpty' => true , 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Идентификатор новости',
            'link_url' => 'Ссылка на источник',
            'title' => 'Заголовок новости',
            'main_content' => 'Основное содержания новости',
            'is_active' => 'Активна',
            'created_at' => 'Время создания',
            'updated_at' => 'Время последнего изменения',
            'category_id' => 'Идентификатор категории',
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getComments() : ActiveQuery {
        return $this->hasMany(Comment::class, ['news_id' => 'id']);
    }
}
<?php


namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id;
 * @property string $context;
 * @property int $news_id;
 * @property int $created_at;
*/

class Comment extends ActiveRecord {

    public static function tableName(): string
    {
        return 'comment';
    }

    public function rules(): array
    {
        return [
            ['news_id', 'integer'],
            [['news_id', 'context'], 'required'],
            ['context', 'trim'],
            ['context', 'string', 'min' => 2, 'tooShort' => 'Comment is too small!']
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'Идентификатор комментария',
            'news_id' => 'Идентификатор новости',
            'context' => 'Содержание',
            'created_at' => 'Время создания'
        ];
    }
}

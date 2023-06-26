<?php


namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
            'id' => 'Comment Id',
            'news_id' => 'News Id for current comment',
            'context' => 'Comment Body',
            'created_at' => 'Comment creation time',
        ];
    }
}

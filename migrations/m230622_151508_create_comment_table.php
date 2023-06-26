<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%comment}}`.
 */
class m230622_151508_create_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%comment}}', [
            'id' => $this->primaryKey()->comment('Comment Id'),
            'news_id' => $this->integer()->notNull()->comment('News Id'),
            'context' => $this->text()->notNull()->comment('Comment text-body'),
            'created_at' => $this->integer()->notNull()->comment("Comment creation time"),
        ]);

        $this->addForeignKey(
            'fk_comment_news_id',
            'comment',
            'news_id',
            'news',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_comment_news_id','{{%comment}}');
        $this->dropTable('{{%comment}}');
    }
}

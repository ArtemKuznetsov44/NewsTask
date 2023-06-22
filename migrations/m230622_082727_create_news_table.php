<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%news}}`.
 */
class m230622_082727_create_news_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->createTable('{{%news}}', [
            'id' => $this->primaryKey(),
            'content' => $this->json()->notNull()->comment('Two fields: "title", "mainContent"'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Is current news published or not'),
            'created_at' => $this->integer()->notNull()->comment('Time of creation'),
            'updated_at' => $this->integer()->comment('Time of last modification'),
            'category_id' => $this->integer()->notNull()->comment('id of category for current news')
        ]);

        $this->addForeignKey(
            'fk_news_category_id',
            'news',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_news_category_id', '{{%news}}');
        $this->dropTable('{{%news}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m230621_202114_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey()->comment('category_id'),
            'name' => $this->string()->notNull()->unique()->comment('category_title')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }
}

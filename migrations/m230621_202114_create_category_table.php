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
            'title' => $this->string()->notNull()->unique()->comment('category_title'),
            'parent_cat_id' => $this->integer()->unique()->comment('Parent category id')
        ]);

        $this->addForeignKey(
            'fk_category_parent_cat_id',
            'category',
            'parent_cat_id',
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
        $this->dropForeignKey('fk_category_parent_cat_id','{{%category}}' );
        $this->dropTable('{{%category}}');
    }
}

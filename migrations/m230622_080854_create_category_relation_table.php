<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_relation}}`.
 */
class m230622_080854_create_category_relation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_relation}}', [
            'parent_cat_id' => $this->integer()->notNull()->comment('id of parent category'),
            'current_cat_id' => $this->integer()->notNull()->comment('id of current category'),
        ]);

        $this->addPrimaryKey(
            $name ='pk_category_relation_parent_cat_id_current_cat_id',
            $table = 'category_relation',
            $columns = ['parent_cat_id', 'current_cat_id']
        );

        $this->addForeignKey(
            'fr_category_relation_parent_cat_id',
            'category_relation',
            'parent_cat_id',
            'category',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_category_relation_current_cat_id',
            'category_relation',
            'current_cat_id',
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
        $this->dropForeignKey('fk_category_relation_current_cat_id','{{%category_relation}}');
        $this->dropForeignKey('fr_category_relation_parent_cat_id','{{%category_relation}}');
        $this->dropPrimaryKey('pk_category_relation_parent_cat_id_current_cat_id','{{%category_relation}}');
        $this->dropTable('{{%category_relation}}');
    }
}

<?php
namespace altiore\user\migrations;

use yii\db\Migration;

class m160610_085723_article_table extends Migration
{
    private $tableName = '{{%article}}';
    private $userTable = '{{%user}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id'         => $this->primaryKey(),
            'title'      => $this->string()->notNull(),
            'text'       => $this->text()->notNull(),
            'creator_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull()->unsigned(),
            'updater_id' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull()->unsigned(),
        ], $tableOptions);

        $this->createIndex('i_article_creator_id', $this->tableName, 'creator_id');
        $this->createIndex('i_article_updater_id', $this->tableName, 'updater_id');

        $this->addForeignKey('fk_article_creator_id', $this->tableName, 'creator_id', $this->userTable, 'id', 'NO ACTION', 'CASCADE');
        $this->addForeignKey('fk_article_updater_id', $this->tableName, 'updater_id', $this->userTable, 'id', 'NO ACTION', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_article_creator_id', $this->tableName);
        $this->dropForeignKey('fk_article_updater_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}

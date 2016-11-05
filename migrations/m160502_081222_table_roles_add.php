<?php

use yii\db\Migration;

/**
 * Class m160502_081222_table_roles_add.
 * @author Razzwan <razvanlomov@gmail.com>
 */
class m160502_081222_table_roles_add extends Migration
{
    private $table = '{{%role}}';
    private $relationTable = '{{%user}}';

    private $relationColumn = 'role_id';

    /**
     * apply migrate
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table, [
            'id' => $this->integer()->notNull(),
            'name' => $this->string(20),
            'permission' => $this->string(),
        ], $tableOptions);

        $this->addPrimaryKey('pr_role_id', $this->table, 'id');

        $this->addColumn($this->relationTable, $this->relationColumn, $this->integer()->notNull());

        $this->insert($this->table, [
            'id'     => 0,
            'name'   => 'guest',
        ]);

        $this->insert($this->table, [
            'id'     => 1,
            'name'   => 'user',
        ]);

        $this->insert($this->table, [
            'id'     => 976,
            'name'   => 'admin',
        ]);

        $this->createIndex('i_user_role_id', $this->relationTable, $this->relationColumn);
    }

    /**
     * revert migrate
     */
    public function safeDown()
    {
        $this->dropColumn($this->relationTable, $this->relationColumn);

        $this->dropTable($this->table);
    }
}

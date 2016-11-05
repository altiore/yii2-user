<?php

use yii\db\Migration;

/**
 * Class m160502_081222_table_roles_add.
 * @author Razzwan <razvanlomov@gmail.com>
 */
class m160502_081223_table_roles_add extends Migration
{
    private $table = '{{%role}}';
    private $relationTable = '{{%user}}';

    private $relationColumn = 'role_id';

    /**
     * apply migrate
     */
    public function safeUp()
    {
        $this->addForeignKey('fk_user_role_id', $this->relationTable, $this->relationColumn, $this->table, 'id', 'RESTRICT', 'CASCADE');
    }

    /**
     * revert migrate
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_role_id', $this->relationTable);
    }
}

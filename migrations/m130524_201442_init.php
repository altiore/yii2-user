<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    private $userTable = '{{%user}}';
    private $passwordResetTokenTable = '{{%password_reset_token}}';

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->userTable, [
            'id'            => $this->primaryKey(),
            'username'      => $this->string()->unique(),
            'email'         => $this->string()->notNull()->unique(),
            'first_name'    => $this->string(),
            'last_name'     => $this->string(),
            'auth_key'      => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'status'        => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at'    => $this->integer()->notNull()->unsigned(),
            'updated_at'    => $this->integer()->notNull()->unsigned(),
        ], $tableOptions);

        $this->createTable($this->passwordResetTokenTable, [
            'token'   => $this->string()->unique(),
            'user_id' => $this->integer()->notNull(),
            'PRIMARY KEY(token)',
        ], $tableOptions);

        $this->createIndex('i_password_reset_token_user_id', $this->passwordResetTokenTable, 'user_id');
        $this->addForeignKey('fk_password_reset_token_user_id', $this->passwordResetTokenTable, 'user_id',
            $this->userTable, 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_password_reset_token_user_id', $this->passwordResetTokenTable);
        $this->dropTable($this->passwordResetTokenTable);
        $this->dropTable($this->userTable);
    }
}

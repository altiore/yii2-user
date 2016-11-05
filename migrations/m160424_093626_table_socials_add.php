<?php

use yii\db\Migration;

class m160424_093626_table_socials_add extends Migration
{
    private $userTable = '{{%user}}';
    private $userSocialTable = '{{%user_social}}';

    /**
     * apply migrate
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->userSocialTable, [
            'user_id'                 => $this->integer()->notNull(),
            'client_id'               => $this->string(20)->notNull(),
            'client_user_id'          => $this->string(40)->notNull(),
            'client_user_email'       => $this->string(),
            'client_user_profile_url' => $this->string(),
            'created_at'              => $this->integer()->notNull()->unsigned(),
            'updated_at'              => $this->integer()->notNull()->unsigned(),
            'PRIMARY KEY(user_id,client_id)',
        ], $tableOptions);

        $this->createIndex('i_user_social_link_client_id', $this->userSocialTable, 'client_id');
        $this->createIndex('i_user_social_link_client_user_id', $this->userSocialTable, 'client_user_id');
        $this->createIndex('i_user_social_user_id', $this->userSocialTable, 'user_id');

        $this->addForeignKey('fk_user_social_user_id', $this->userSocialTable, 'user_id', $this->userTable, 'id',
            'CASCADE',
            'CASCADE');

    }

    /**
     * revert migrate
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_social_user_id', $this->userSocialTable);
        $this->dropTable($this->userSocialTable);
    }
}

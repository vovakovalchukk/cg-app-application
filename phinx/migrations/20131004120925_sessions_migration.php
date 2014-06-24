<?php

use Phinx\Migration\AbstractMigration;

class RemoveSessions extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('sessions', array('id' => false, 'primary_key' => 'session_id'));
        $table->addColumn('session_id', 'string', array('limit' => 32))
            ->addColumn('session_data', 'text')
            ->addColumn('session_expiration', 'integer', array('limit' => 11))
            ->create();
    }

    public function down()
    {
        $this->dropTable('sessions');
    }

}
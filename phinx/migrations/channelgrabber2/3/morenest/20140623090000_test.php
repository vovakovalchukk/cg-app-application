<?php

use Phinx\Migration\AbstractMigration;

class Test extends AbstractMigration
{
    public function up()
    {
        $table = $this->query("CREATE TABLE IF NOT EXISTS `session` (
                                  `id` char(32) NOT NULL DEFAULT '',
                                  `name` char(32) NOT NULL DEFAULT '',
                                  `modified` int(11) DEFAULT NULL,
                                  `lifetime` int(11) DEFAULT NULL,
                                  `data` text,
                                  PRIMARY KEY (`id`,`name`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    }

    public function down()
    {
        $this->dropTable('session');
    }

}
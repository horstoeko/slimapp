<?php

namespace horstoeko\slimapp\dbmigrations;

use Phoenix\Migration\AbstractMigration;

class Initialization extends AbstractMigration
{
    protected function up(): void
    {
        $this->table('slimapp_users', 'id')
            ->setCharset('utf8')
            ->setCollation('utf8_general_ci')
            ->addColumn('id', 'biginteger', ['autoincrement' => true])
            ->addColumn('username', 'string', ['default' => '', 'length' => 50])
            ->addColumn('password', 'string', ['default' => '', 'length' => 1024])
            ->addColumn('firstname', 'string', ['default' => '', 'length' => 1024])
            ->addColumn('lastname', 'string', ['default' => '', 'length' => 1024])
            ->addColumn('email', 'string', ['default' => '', 'length' => 1024])
            ->addColumn('admin', 'tinyinteger', ['default' => 0, 'length' => 4])
            ->create();
    }

    protected function down(): void
    {
        $this->table('slimapp_users')
            ->drop();
    }
}

<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class DatabaseServiceMakeCommand extends Command
{
    protected $signature = 'make:database-service';

    protected $description = '为整个数据库创建 service';

    public function fire()
    {
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $table = array_values((array) $table)[0];
            $this->call('make:service', ['name' => $table, '--fillable' => true]);
        }
    }
}

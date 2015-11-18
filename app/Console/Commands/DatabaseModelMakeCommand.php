<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class DatabaseModelMakeCommand extends Command
{
    protected $signature = 'make:database-model';

    protected $description = '为整个数据库创建 model';

    public function fire()
    {
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $table = array_values((array) $table)[0];
            $this->call('make:tmodel', ['name' => $table, '--fillable' => true]);
        }
    }
}

<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class TModelMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:tmodel';

    protected $description = '创建 Eloquent';

    protected $type = 'Model';

    protected $prefix;
    protected $fullTable;
    protected $table;

    public function fire()
    {
        parent::fire();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Models';
    }

    protected function getStub()
    {
        return base_path('resources/stubs/tmodel.stub');
    }

    protected function getNameInput()
    {
        $prefix = $this->option('prefix');
        $prefix = $prefix ?: DB::getTablePrefix();
        $this->prefix = $prefix;

        $table = $this->argument('name');
        $this->type = 'Model for '.$table;
        $this->fullTable = $table;
        if (starts_with($table, $prefix)) {
            $table = substr($table, strlen($prefix));
        }
        $this->table = $table;

        $modelName = ucfirst(Str::camel(Str::singular($table)));

        return $modelName;
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);
        $class = str_replace(
            'DummyTable', "'".$this->table."'", $class
        );

        $class = $this->replaceFillable($class);

        return $class;
    }

    protected function replaceFillable($class)
    {
        $fillable = $this->option('fillable');
        if ($fillable) {
            $tableColumns = DB::select('DESCRIBE '.$this->fullTable);
            $columns = [];
            $notFillable = ['id', 'deleted_at', 'created_at', 'updated_at'];
            foreach ($tableColumns as $column) {
                $columns[] = $column->Field;
            }
            $fillable = array_values(array_diff($columns, $notFillable));
        } else {
            $fillable = [];
        }
        $fillable = array_map(function ($value) {
            return "'".$value."'";
        }, $fillable);
        $class = str_replace('DummyFillable', "[\n\t\t".implode($fillable, ",\n\t\t")."\n\t]", $class);

        return $class;
    }

    protected function getOptions()
    {
        $parentOptions = parent::getOptions();
        $options = [
            ['prefix', null, InputOption::VALUE_OPTIONAL, '数据库表前缀'],
            ['fillable', 'f', InputOption::VALUE_NONE, '是否根据数据库表创建 fillable 属性'],
        ];

        return array_merge($parentOptions, $options);
    }
}

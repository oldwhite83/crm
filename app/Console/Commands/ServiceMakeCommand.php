<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ServiceMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:service';

    protected $description = '创建 Service';

    protected $type = 'Service';

    protected $prefix;
    protected $fullTable;
    protected $table;
    protected $modelName;

    public function fire()
    {
        parent::fire();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    protected function getStub()
    {
        return base_path('resources/stubs/service.stub');
    }

    protected function getNameInput()
    {
        $prefix = $this->option('prefix');
        $prefix = $prefix ?: DB::getTablePrefix();
        $this->prefix = $prefix;

        $table = $this->argument('name');
        $this->type = 'Service for '.$table;
        $this->fullTable = $table;
        if (starts_with($table, $prefix)) {
            $table = substr($table, strlen($prefix));
        }
        $this->table = $table;

        $modelName = ucfirst(Str::camel(Str::singular($table)));
        $this->modelName = $modelName;

        return $modelName.'Service';
    }

    protected function getDummyModel()
    {
        return 'App\\Models\\'.$this->modelName;
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);
        $class = str_replace(
            'DummyModel', $this->getDummyModel(), $class
        );
        $class = str_replace(
            'DummyMName', $this->modelName, $class
        );

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

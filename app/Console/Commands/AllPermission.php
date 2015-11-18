<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class AllPermission extends Command
{
    protected $signature = 'permission:all {role_id}';

    protected $description = '为角色授予所有权限.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $role = Role::find($this->argument('role_id'));
        if (empty($role)) {
            $this->error('未找到该角色');

            return;
        }
        if (!$this->confirm('确定为 "<comment>'.$role->name.'</comment>" 授权? ')) {
            $this->comment('取消授权');

            return;
        }

        $permissions = Permission::lists('id')->toArray();
        $role->perms()->sync($permissions);

        $this->info('授权成功');
    }
}

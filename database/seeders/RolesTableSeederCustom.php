<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;

class RolesTableSeederCustom extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $role = $this->getRole(4);
        if (!$role->exists) {
            $role->fill(["name" => "pos", "display_name" => "POS"])->save();
        }
        
        $role = $this->getRole(5);
        if (!$role->exists) {
            $role->fill(["name" => "scanner", "display_name" => "Scanner"])->save();
        }
        
        $role = $this->getRole(6);
        if (!$role->exists) {
            $role->fill(["name" => "manager", "display_name" => "Manager"])->save();
        }
    }

    /**
     * [dataRow description].
     *
     * @param [type] $type  [description]
     * @param [type] $field [description]
     *
     * @return [type] [description]
     */
    protected function getRole($id)
    {
        return Role::firstOrNew([
            'id'           => $id,
        ]);
    }
    
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataType;

class DataTypesTableSeederCustom extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $singular       = 'promocode';
        $slug           = 'promocodes';
        $dataType       = $this->dataType('slug', $slug);
        if (!$dataType->exists) {
            $dataType->fill([
                'name'                  => $slug,
                'slug'                  => $slug,
                'display_name_singular' => ucfirst($singular),
                'display_name_plural'   => ucfirst($slug),

                'icon'                  => 'voyager-tag',
                'model_name'            => 'App\\Models\\Promocode',
                'policy_name'           => NULL,
                'controller'            => NULL,
                'description'           => NULL,
                'generate_permissions'  => 1,
                'server_side'           => 1,
                'details'               => '{"order_column":"id","order_display_column":"id","order_direction":"asc","default_search_key":"title","scope":null}',
            ])->save();
        }
    }

     /**
     * [dataType description].
     *
     * @param [type] $field [description]
     * @param [type] $for   [description]
     *
     * @return [type] [description]
     */
    protected function dataType($field, $for)
    {
        return DataType::firstOrNew([$field => $for]);
    }

}
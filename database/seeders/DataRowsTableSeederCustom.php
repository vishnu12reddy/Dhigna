<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

class DataRowsTableSeederCustom extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $this->promocodesCustom();
    }

    protected function promocodesCustom()
    {
        $DataType      = DataType::where('slug', 'promocodes')->firstOrFail();

        // add rows (auto-generated)
        $dataRow = $this->dataRow($DataType, "id");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "number", "display_name" => "Id", "required" => 1, "browse" => 1, "read" => 1, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 1, ])->save();
        }

        $dataRow = $this->dataRow($DataType, "code");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "text", "display_name" => "Code", "required" => 1, "browse" => 1, "read" => 1, "edit" => 1, "add" => 1, "delete" => 1, "details" =>  [
            "validation" => [
                    "rule" => "required|max:32|unique:promocodes,code,1" 
                ] 
            ], "order" => 2, ])->save();
        }        
        
        $dataRow = $this->dataRow($DataType, "reward");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "text", "display_name" => "Reward (e.g 5.00)", "required" => 1, "browse" => 1, "read" => 1, "edit" => 1, "add" => 1, "delete" => 1, "details" =>  [
            "validation" => [
                    "rule" => "required" 
                ] 
            ], "order" => 3, ])->save();
        }        
        
        $dataRow = $this->dataRow($DataType, "quantity");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "number", "display_name" => "Quantity", "required" => 1, "browse" => 1, "read" => 1, "edit" => 1, "add" => 1, "delete" => 1, "details" =>  [
            "validation" => [
                    "rule" => "required" 
                ] 
            ], "order" => 4, ])->save();
        }        

        $dataRow = $this->dataRow($DataType, "p_type");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "select_dropdown", "display_name" => "Type", "required" => 1, "browse" => 1, "read" => 1, "edit" => 1, "add" => 1, "delete" => 1, "details" => [
            "default" => "fixed", 
            "options" => [
                    "fixed" => "Fixed", 
                    "percent" => "Percent"
                ], 
            "validation" => [
                        "rule" => "required|in:fixed,percent" 
                    ] 
            ], "order" => 5, ])->save();
        }
          
        $dataRow = $this->dataRow($DataType, "data");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "text", "display_name" => "Data", "required" => 0, "browse" => 0, "read" => 0, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 6, ])->save();
        }  

        $dataRow = $this->dataRow($DataType, "is_disposable");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "text", "display_name" => "Is Disposable", "required" => 0, "browse" => 0, "read" => 0, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 7, ])->save();
        }

        $dataRow = $this->dataRow($DataType, "expires_at");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "timestamp", "display_name" => "Expires At", "required" => 0, "browse" => 1, "read" => 1, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 8, ])->save();
        }

        $dataRow = $this->dataRow($DataType, "status");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "select_dropdown", "display_name" => "Status", "required" => 1, "browse" => 1, "read" => 1, "edit" => 1, "add" => 1, "delete" => 1, "details" =>  [
        "default" => "1", 
        "options" => [
                "1" =>"Enabled",
                "0" =>"Disabled"
            ], 
        "validation" => [
                    "rule" => "required" 
                ] 
        ], "order" => 9, ])->save();
        }

        $dataRow = $this->dataRow($DataType, "created_at");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "timestamp", "display_name" => "Created At", "required" => 0, "browse" => 0, "read" => 1, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 10, ])->save();
        }
        $dataRow = $this->dataRow($DataType, "updated_at");
        if (!$dataRow->exists) {
            $dataRow->fill(["type" => "timestamp", "display_name" => "Updated At", "required" => 0, "browse" => 1, "read" => 1, "edit" => 0, "add" => 0, "delete" => 0, "details" => "{}", "order" => 11, ])->save();
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
    protected function dataRow($type, $field)
    {
        return DataRow::firstOrNew([
            'data_type_id' => $type->id,
            'field'        => $field,
        ]);
    }
    
}
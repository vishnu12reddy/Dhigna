<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;

class MenuItemsTableSeederCustom extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();

        $menuItem = MenuItem::firstOrNew(["menu_id" => $menu->id, "title" => "Promocodes", "url" => "", "route" => "voyager.promocodes.index", ]);
        if (!$menuItem->exists) {
            $menuItem->fill(["target" => "_self", "icon_class" => "voyager-tag", "color" => "", "parent_id" => null, "order" => "15", ])->save();
        }

        $menuItem = MenuItem::firstOrNew(["menu_id" => $menu->id, "title" => "Complimentary Bookings", "url" => "", "route" => "voyager.bookings.bulk_bookings", ]);
        if (!$menuItem->exists) {
            $menuItem->fill(["target" => "_self", "icon_class" => "voyager-puzzle", "color" => "", "parent_id" => null, "order" => "16", ])->save();
        }
    }

    protected function menuItem($field, $for)
    {
        return MenuItem::firstOrNew([$field => $for]);
    }


}
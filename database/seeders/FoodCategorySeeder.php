<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\FoodCategory;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $food_categories = array(
            array('name' => 'Soup'),
            array('name' => 'Salad'),
            array('name' => 'Starter'),
            array('name' => 'Main'),
            array('name' => 'Dessert'),
            array('name' => 'Snacks'),
            array('name' => 'Party Food'),
            array('name' => 'Drinks'),
        );

        DB::table('food_categories')->insert($food_categories);
    }
}

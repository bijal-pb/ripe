<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\FoodMethod;

class FoodMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $food_methods = array(
              array('name' => 'Roasting/Baking'),
              array('name' => 'Broiling'),
              array('name' => 'Frying'),
              array('name' => 'Steaming'),
              array('name' => 'Boiling'),
              array('name' => 'Summering'),
              array('name' => 'Braising'),
              array('name' => 'Poaching'),
              array('name' => 'Blanching'),
              array('name' => 'Grilling'),
              array('name' => 'Sauteing'),
              array('name' => 'Searing'),
              array('name' => 'Pressure Cooking/Slow Cooking'),
              array('name' => 'Stewing'),
              array('name' => 'Stir Fry'),
          );

		  DB::table('food_methods')->insert($food_methods);
    }
}

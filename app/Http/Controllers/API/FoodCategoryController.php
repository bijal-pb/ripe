<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;
use App\Models\FoodCategory;
use Auth;

class FoodCategoryController extends Controller
{
    use ApiTrait;
     /**
     * @OA\Get(
     *     path="/api/food/categories/list",
     *     tags={"Food"},
     *     security={{"bearer_token":{}}},  
     *     summary="Get food categories list",
     *     security={{"bearer_token":{}}},
     *     operationId="Listing Food Categories",
     *    
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function food_categories_list(Request $request){
		try{
                $food_category = FoodCategory::get();
				return $this->response($food_category,'Food Categories List');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }
}

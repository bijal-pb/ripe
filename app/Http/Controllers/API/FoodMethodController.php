<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;
use App\Models\FoodMethod;
use Auth;

class FoodMethodController extends Controller
{
    use ApiTrait;
     /**
     * @OA\Get(
     *     path="/api/food/method/list",
     *     tags={"Food"},
     *     security={{"bearer_token":{}}},  
     *     summary="Get food methods list",
     *     security={{"bearer_token":{}}},
     *     operationId="Listing Food Methods",
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
    public function food_methods_list(Request $request){
		try{
                $food_method = FoodMethod::get();
				return $this->response($food_method,'Food Methods List');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }
}

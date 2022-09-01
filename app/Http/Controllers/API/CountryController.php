<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;
use App\Models\Country;

class CountryController extends Controller
{
    use ApiTrait;

    /**
     *  @OA\Get(
     *     path="/api/countries",
     *     tags={"Country"},
     *     security={{"bearer_token":{}}},  
     *     summary="Country List",
     *     operationId="country-list",
     * 
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
    public function list()
    {
        $countries = Country::select('id','name','code','second_name')->where('second_name','!=',null)->orderBy('name','asc')->get();

        return $this->response($countries, 'Country Lists');
    }
}

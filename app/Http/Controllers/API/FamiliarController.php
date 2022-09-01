<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiTrait;
use App\Models\Familiar;

class FamiliarController extends Controller
{
    use ApiTrait;

    /**
     *  @OA\Get(
     *     path="/api/familiar/list",
     *     tags={"Familier Racipies"},
     *     summary="Familier Racipies List",
     *     operationId="familier racipies-list",
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
    public function familier_list()
    {
        $familier = Familiar::get();

        return $this->response($familier, 'Familier recipies Lists');
    }
}

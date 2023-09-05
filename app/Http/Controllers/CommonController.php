<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Exception;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getLabs(){
        try{
            $labs=Lab::get();

            return response()->json([
                'message' => 'Fetched labs',
                'labs' => $labs
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }
}

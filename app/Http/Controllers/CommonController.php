<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Lab;
use Exception;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getLabs(){
        try{
            $labs=Lab::with('difficultyInfo')->get();

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
    
    public function getBadges(){
        try{
            $badges=Badge::get();
            if ($badges->isEmpty()) {
                return response()->json([
                    'message' => 'No badges exist'
                ], 404);
            } else{
                return response()->json([
                    'message' => "Badges found.",
                    'badges' => $badges
                ],500);
            }           
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }
}

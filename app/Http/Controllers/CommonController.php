<?php

namespace App\Http\Controllers;

use App\Models\ActiveLab;
use App\Models\Badge;
use App\Models\CompletedLab;
use App\Models\Lab;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    
    public function getAllLabs(){
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
    public function topTen()
    {
        try {
            $users = User::where('type_id', 3)
                ->orderBy('rewards', 'desc')
                ->take(10)
                ->withCount(['completedLabs', 'badges'])
                ->get();
    
            $users = $users->map(function ($user) {
                $user->rank = $user->rank();
                return $user;
            });
    
            return response()->json([
                'message' => 'Top 10 users',
                'users' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    
    
    public function getBadges(){
        try{
            $badges=Badge::all();
            if ($badges->isEmpty()) {
                return response()->json([
                    'message' => 'No badges exist'
                ], 404);
            } else{
                return response()->json([
                    'message' => "Badges found.",
                    'badges' => $badges
                ],200);
            }           
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }
}

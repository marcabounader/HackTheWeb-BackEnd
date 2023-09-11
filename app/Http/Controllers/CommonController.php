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
    public function getLabs(){
        try{
            $labs=Lab::with('difficultyInfo')->get();
            foreach ($labs as $lab) {
                $lab->isActive = ActiveLab::where([['user_id','=',Auth::id()],['lab_id','=',$lab->id]])
                    ->exists();
                $lab->isComplete = CompletedLab::where([['user_id','=',Auth::id()],['lab_id','=',$lab->id]])
                ->exists();
            }
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

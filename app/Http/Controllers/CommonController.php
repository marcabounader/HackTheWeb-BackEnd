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
    
    public function getAllLabs($page = 1, $labs_per_page = 10){
        try{
            $offset = ($page - 1) * $labs_per_page;

            $labs=Lab::with('difficultyInfo')
            ->skip($offset)
            ->take($labs_per_page)
            ->get();

            $total_labs = Lab::count();

            $total_pages = ceil($total_labs / $labs_per_page);

            return response()->json([
                'message' => 'Fetched labs',
                'labs' => $labs,
                'total_pages' => $total_pages,
                'currentPage' => $page,
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

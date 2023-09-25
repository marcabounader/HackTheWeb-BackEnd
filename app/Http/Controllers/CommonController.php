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
            $labs = Lab::with('difficultyInfo')->paginate(9);

            return response()->json([
                'message' => 'Fetched labs',
                'labs' => $labs,
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }

    public function searchLabs(Request $request) {
        try {
            $user = Auth::user();
            $query=$request->input('query');
            if (empty($query)) {
                return response()->json([
                    'message' => 'Search query is empty.'
                ], 400);
            }
            $query = Lab::where('name', 'like', '%' . $query . '%')->with('difficultyInfo')->paginate(9);
            if ($query->isEmpty()) {
                return response()->json([
                    'message' => 'No labs with this name.'
                ], 204);
            }
            if ($user->type_id == 3) {
                foreach ($query as $lab) {
                    $lab->isActive = ActiveLab::where([['user_id', '=', Auth::id()], ['lab_id', '=', $lab->id]])
                        ->exists();
                    $lab->isComplete = CompletedLab::where([['user_id', '=', Auth::id()], ['lab_id', '=', $lab->id]])
                        ->exists();
                    $lab->active_lab = ActiveLab::where([['user_id', '=', Auth::id()], ['lab_id', '=', $lab->id]])
                        ->first();
                }
            }
    
            return response()->json([
                'message' => "Labs found.",
                'labs' => $query
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function searchBadges(Request $request) {
        try {
            $query=$request->input('query');
            if (empty($query)) {
                return response()->json([
                    'message' => 'Search query is empty.'
                ], 400);
            }
            $query = Badge::where('name', 'like', '%' . $query . '%')->paginate(4);
            if ($query->isEmpty()) {
                return response()->json([
                    'message' => 'No badges with this name.'
                ], 204);
            }

            return response()->json([
                'message' => "Labs found.",
                'badges' => $query
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getLabsInfo(){
        try{
            $labs = Lab::select('id','name')->get();

            return response()->json([
                'message' => 'Fetched labs',
                'labs' => $labs,
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
            $badges=Badge::paginate(4);
            
            if ($badges->isEmpty()) {
                return response()->json([
                    'message' => 'No badges exist'
                ], 204);
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

<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Exception;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function addLab(Request $request){

        try{
            $lab=new Lab;
            $lab->category_id=$request->category_id;
            $lab->difficulty_id=$request->difficulty_id;
            $lab->name=$request->name;
            $lab->objective=$request->objective;
            $lab->launch_api=$request->launch_api;
            $lab->score=$request->score;
            if ($lab->save()) {
                return response()->json([
                    'message' => 'Lab added',
                    'lab' => $lab
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to add lab'
                ], 500);
            }
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }

    public function deleteLab($id){

        try{
            $lab = Lab::find($id);

            if (!$lab) {
                return response()->json([
                    'message' => 'Lab not found'
                ], 404);
            }
        
            if ($lab->delete()) {
                return response()->json([
                    'message' => 'Lab deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete lab'
                ], 500);
            }
        } catch(Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ],500);
        } 
    }
}

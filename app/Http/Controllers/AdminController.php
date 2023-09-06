<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Lab;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    public function addBadge(Request $request)
    {
        try {
            $badge = new Badge;
            $badge->category_id = $request->category_id;
            $badge->name = $request->name;
    
            $base64Image = $request->input('icon');
            $binaryData = base64_decode($base64Image);
            $original_file_name=$request->file_name;
            $tempFilePath = tempnam(sys_get_temp_dir(), 'temp_base64');
            file_put_contents($tempFilePath, $binaryData);
            $uploadedFile = new UploadedFile(
                $tempFilePath,
                $original_file_name, 
                mime_content_type($tempFilePath), // Get the MIME type
                null,
                true // Delete the file after validation
            );
            $validationRules = [
                'icon' => 'required|image|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            ];
    
            $validator = \Validator::make(['icon' => $uploadedFile], $validationRules);
    
            if ($validator->fails()) {
                // Validation failed, return an error response
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid image file.',
                ], 400);
            }
    
            // Proceed with uploading and saving the image
            $fileExtension = $uploadedFile->getClientOriginalExtension();
            $fileName = uniqid() . '.' . $fileExtension;
    
            Storage::disk('public')->put('badges/' . $fileName, $binaryData);
            $publicUrl = Storage::disk('public')->url('badges/' . $fileName);
            $badge->icon_url = $publicUrl;
    
            $badge->save();
    
            return response()->json([
                'status' => '200',
                'badge' => $badge
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function deleteBadge($id)
    {
        try {
            $badge = Badge::find($id);
    
            if (!$badge) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Badge not found.',
                ], 404);
            }
    
            $iconUrl = $badge->icon_url;
    
            $badge->delete();
    
            $filename = basename($iconUrl);
    
            Storage::disk('public')->delete('badges/' . $filename);
    
            return response()->json([
                'message' => 'Badge deleted successfully.',
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
}

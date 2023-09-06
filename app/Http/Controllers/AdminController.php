<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Lab;
use App\Models\LabCategory;
use App\Models\LabDifficulty;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            // $tempFilePath = tempnam(sys_get_temp_dir(), 'temp_base64');
            // file_put_contents($tempFilePath, $binaryData);
            // $uploadedFile = new UploadedFile(
            //     $tempFilePath,
            //     $original_file_name, 
            //     mime_content_type($tempFilePath), // Get the MIME type
            //     null,
            //     true // Delete the file after validation
            // );
            // Use finfo to determine the MIME type based on file content
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $binaryData);
            finfo_close($finfo);
            $allowedMimeTypes = ['image/jpeg','image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $maxFileSize = 2048; // 2 MB
    
            if (!in_array($mimeType, $allowedMimeTypes) || strlen($binaryData) > ($maxFileSize * 1024)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid image file.',
                ], 400);
            }
    

            $fileExtension = explode('/', $mimeType)[1]; // Get the file extension from MIME type

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
    public function getLabCategories()
    {
        try {
            // Retrieve all lab categories from the database
            $labCategories = LabCategory::all();

            if ($labCategories->isEmpty()) {
                return response()->json([
                    'message' => 'No lab categories found',
                ], 404);
            }

            return response()->json([
                'message' => 'Lab categories found',
                'lab_categories' => $labCategories,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function addLabCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:lab_categories',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $category = new LabCategory;
            $category->name = $request->name;

            if ($category->save()) {
                return response()->json([
                    'message' => 'Lab category added',
                    'category' => $category
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to add lab category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteLabCategory($id)
    {
        try {
            $category = LabCategory::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Lab category not found'
                ], 404);
            }

            if ($category->delete()) {
                return response()->json([
                    'message' => 'Lab category deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete lab category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getLabDifficulties()
    {
        try {
            $difficulties = LabDifficulty::get();
            if ($difficulties->isEmpty()) {
                return response()->json([
                    'message' => 'No lab difficulties exist'
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Lab difficulties found',
                    'difficulties' => $difficulties
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addLabDifficulty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:lab_difficulties',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $difficulty = new LabDifficulty;
            $difficulty->name = $request->name;

            if ($difficulty->save()) {
                return response()->json([
                    'message' => 'Lab difficulty added',
                    'difficulty' => $difficulty
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to add lab difficulty'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteLabDifficulty($id)
    {
        try {
            $difficulty = LabDifficulty::find($id);

            if (!$difficulty) {
                return response()->json([
                    'message' => 'Lab difficulty not found'
                ], 404);
            }

            if ($difficulty->delete()) {
                return response()->json([
                    'message' => 'Lab difficulty deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete lab difficulty'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


}

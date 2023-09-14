<?php

namespace App\Http\Controllers;

use App\Models\ActiveLab;
use App\Models\Badge;
use App\Models\BadgeCategory;
use App\Models\Lab;
use App\Models\LabCategory;
use App\Models\LabDifficulty;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function getActiveLabs()
    {
        try {
            $active_labs = ActiveLab::all();
            $active_labs->load('userInfo');
            if ($active_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No active labs'
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Active labs found',
                    "active_labs" => $active_labs
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function modifyLab(Request $request, $lab_id)
    {
        try {
            $lab = Lab::find($lab_id);
    
            if (!$lab) {
                return response()->json([
                    'message' => 'Lab not found',
                ], 404);
            }
    
            if ($request->has('category_id')) {
                $lab->category_id = $request->category_id;
            }
    
            if ($request->has('difficulty_id')) {
                $lab->difficulty_id = $request->difficulty_id;
            }
    
            if ($request->has('name')) {
                $lab->name = $request->name;
            }
    
            if ($request->has('objective')) {
                $lab->objective = $request->objective;
            }
    
            if ($request->has('launch_api')) {
                $lab->launch_api = $request->launch_api;
            }
    
            if ($request->has('reward')) {
                $lab->reward = $request->reward;
            }
    
            if ($request->has('icon')) {
                $base64Image = $request->input('icon');
                $binaryData = base64_decode($base64Image);
    
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
        
                Storage::disk('public')->put('lab-icons/' . $fileName, $binaryData);
                $publicUrl = Storage::disk('public')->url('lab-icons/' . $fileName);
                $lab->icon_url = $publicUrl;
            }
    
            if ($lab->save()) {
                $lab->load('difficultyInfo');
    
                return response()->json([
                    'message' => 'Lab modified',
                    'lab' => $lab,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to modify lab',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function addLab(Request $request){

        try{
            $lab=new Lab;
            $lab->category_id=$request->category_id;
            $lab->difficulty_id=$request->difficulty_id;
            $lab->name=$request->name;
            $lab->objective=$request->objective;
            $lab->launch_api=$request->launch_api;
            $lab->reward=$request->reward;
            $base64Image = $request->input('icon');
            $binaryData = base64_decode($base64Image);

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
    
            Storage::disk('public')->put('lab-icons/' . $fileName, $binaryData);
            $publicUrl = Storage::disk('public')->url('lab-icons/' . $fileName);
            $lab->icon_url = $publicUrl;

            if ($lab->save()) {
                $lab->load('difficultyInfo');

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

    public function statistics(){
        try{
            $lab_count = Lab::count();
            $badge_count = Badge::count();
            $user_count = User::count();
            $active_lab_count = ActiveLab::count();
            $dockerCommand = 'docker ps -q | Measure-Object | Select-Object -ExpandProperty Count';
            $activeContainersCount = (int) trim(shell_exec("powershell.exe -command \"$dockerCommand\""));
            return response()->json([
                "message" => 'Statistics created',
                'lab_count' => $lab_count,
                'badge_count' => $badge_count,
                'user_count' => $user_count,
                'active_lab_count' => $active_lab_count,
                'active_docker_count' => $activeContainersCount
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
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

    public function addBadge(Request $request)
    {
        try {
            $badge = new Badge;
            $badge->category_id = $request->category_id;
            $badge->name = $request->name;
    
            $base64Image = $request->input('icon');
            $binaryData = base64_decode($base64Image);

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
                'category' => 'required|string|unique:lab_categories',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $category = new LabCategory;
            $category->category = $request->category;

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
                'difficulty' => 'required|string|unique:lab_difficulties',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $difficulty = new LabDifficulty;
            $difficulty->difficulty = $request->difficulty;

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

    public function getBadgeCategories()
    {
        try {
            $categories = BadgeCategory::get();
            if ($categories->isEmpty()) {
                return response()->json([
                    'message' => 'No badge categories exist'
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Badge categories found',
                    'categories' => $categories
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addBadgeCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'required|string|unique:badge_categories',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $category = new BadgeCategory;
            $category->category = $request->category;

            if ($category->save()) {
                return response()->json([
                    'message' => 'Badge category added',
                    'category' => $category
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to add badge category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteBadgeCategory($id)
    {
        try {
            $category = BadgeCategory::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Badge category not found'
                ], 404);
            }

            if ($category->delete()) {
                return response()->json([
                    'message' => 'Badge category deleted'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete badge category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function modifyLabDifficulty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:lab_difficulties,id',
                'difficulty' => 'required|string|unique:lab_difficulties,difficulty,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $difficulty = LabDifficulty::find($request->id);
            $difficulty->difficulty = $request->difficulty;

            if ($difficulty->save()) {
                return response()->json([
                    'message' => 'Lab difficulty modified',
                    'difficulty' => $difficulty
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to modify lab difficulty'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function modifyLabCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:lab_categories,id',
                'category' => 'required|string|unique:lab_categories,category,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $category = LabCategory::find($request->id);
            $category->category = $request->category;

            if ($category->save()) {
                return response()->json([
                    'message' => 'Lab category modified',
                    'category' => $category
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to modify lab category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function modifyBadgeCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:badge_categories,id',
                'category' => 'required|string|unique:badge_categories,category,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $category = BadgeCategory::find($request->id);
            $category->category = $request->category;

            if ($category->save()) {
                return response()->json([
                    'message' => 'Badge category modified',
                    'category' => $category
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to modify badge category'
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

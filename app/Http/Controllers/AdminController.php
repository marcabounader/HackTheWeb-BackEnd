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
    public function stopUserLab($project_name)
    {
        $active_lab=ActiveLab::where('project_name',$project_name)->first();
        // Path to the user's Docker Compose file
        $user_id=$active_lab->user_id;
        if($project_name=="mutillidae_sqli_$user_id")
        {
            $userDockerDir = storage_path("mutillidae-docker-master/www-sqli/user-instances/$user_id");

        } else if($project_name=="mutillidae_ci_$user_id"){
            $userDockerDir = storage_path("mutillidae-docker-master/www-ci/user-instances/$user_id");
        } else if($project_name=="mutillidae_jwt_$user_id"){
            $userDockerDir = storage_path("mutillidae-docker-master/www-jwt/user-instances/$user_id");
        }
        $dockerComposeFile = "$userDockerDir/docker-compose.yml";
    
        // Stop and remove containers using docker-compose
        $command = "docker-compose -f $dockerComposeFile -p $project_name down 2>&1";
        exec($command, $output, $exitCode);
    
        if ($exitCode === 0) {

    
            if (!$active_lab) {
                return response()->json([
                    'message' => 'Lab instance not found'
                ], 404);
            }
    
            if ($active_lab->delete()) {
                return response()->json([
                    'message' => "Lab instance stopped for user ID {$user_id}",
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to delete lab instance'
                ], 500);
            }
        } else {
            // The command encountered an error
            return response()->json([
                'message' => "Error stopping instance for user ID {$user_id}",
                'output' => $output, // Capture the command output
            ]);
        }
    }
    public function getActiveLabs()
    {
        try {
            $active_labs = ActiveLab::with('userInfo')->paginate(5);
            if ($active_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No active labs'
                ], 204);
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
    public function searchActiveLabs(Request $request)
    {
        try {
            $query = $request->input('query');
            $perPage=$request->input('perPage');
            if (empty($query)) {
                $active_labs = ActiveLab::with('userInfo')
                ->paginate($perPage);
            } else {
                $active_labs = ActiveLab::with('userInfo')
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                        ->whereHas('userInfo', function ($userQuery) use ($query) {
                            $userQuery->where('name', 'like', '%' . $query . '%')
                                ->orWhere('email', 'like', '%' . $query . '%');
                        })
                        ->orWhere('project_name', 'like', '%' . $query . '%');
                })
                ->paginate($perPage);
            }
            if ($active_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No active labs'
                ], 204);
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
    
    public function restrict($user_id)
    {
        try {
            $user = User::find($user_id);
    
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            if(!$user->is_restricted){
                $update_success=$user->update(['is_restricted' => true]);
                if($update_success){
                    return response()->json([
                        'message' => 'restricted'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Failed to restrict user'
                    ], 500);
                }
            } else {
                $update_success=$user->update(['is_restricted' => false]);
                if($update_success){
                    return response()->json([
                        'message' => 'unrestricted'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Failed to unrestrict user'
                    ], 500);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getUsers()
    {
        try {
            
            $usersPaginator = User::where('type_id', 3)->paginate(5);

            if ($usersPaginator->isEmpty()) {
                return response()->json([
                    'message' => 'No users'
                ], 204);
            } else {
                $users = $usersPaginator->items();
                foreach ($users as $user) {
                    $user->rank = $user->rank();
                }
                return response()->json([
                    'message' => 'Users found',
                    'users' => $usersPaginator
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
    
    public function modifyBadge(Request $request, $badge_id)
    {
        try {
            $badge = Badge::find($badge_id);
    
            if (!$badge) {
                return response()->json([
                    'message' => 'Badge not found',
                ], 404);
            }
    
            if ($request->has('category_id')) {
                $badge->category_id = $request->category_id;
            }

    
            if ($request->has('name')) {
                $badge->name = $request->name;
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
                $badge->icon_url = $publicUrl;
            }
    
            if ($badge->save()) {
    
                return response()->json([
                    'message' => 'Badge modified',
                    'badge' => $badge,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Failed to modify Badge',
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
            $user_count = User::where('type_id',3)->count();
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
            $iconUrl = $lab->icon_url;
        
            $filename = basename($iconUrl);
    
            // Storage::disk('public')->delete('lab-icons/' . $filename);
    
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
            if($request->has('lab_id')){
                if(!empty($request->lab_id)){
                    $badge->lab_id=$request->lab_id;
                }
            }
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
    
            // Storage::disk('public')->delete('badges/' . $filename);
    
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
                ], 204);
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

    public function searchUsers(Request $request) {
        try {
            $user = Auth::user();
            $query=$request->input('query');
            $perPage=$request->input('perPage');
            if (empty($query)) {
                $queryResult = User::
                where('type_id',3)
                ->paginate($perPage);
            } else {
                $queryResult = User::where([
                    ['type_id', '=', 3],
                    ['name', 'like', '%' . $query . '%']
                ])
                ->orWhere([
                    ['type_id', '=', 3],
                    ['email', 'like', '%' . $query . '%']
                ])
                ->paginate($perPage);
            }

            if ($queryResult->isEmpty()) {
                return response()->json([
                    'message' => 'No users with this name.'
                ], 204);
            }
            $users = $queryResult->items();
            foreach ($users as $user) {
                $user->rank = $user->rank();
            }
    
            return response()->json([
                'message' => "Users found.",
                'users' => $queryResult
            ], 200);
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
                ], 204);
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
                ], 204);
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
            $categories = BadgeCategory::all();
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

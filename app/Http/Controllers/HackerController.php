<?php

namespace App\Http\Controllers;

use App\Models\ActiveLab;
use App\Models\Badge;
use App\Models\CompletedLab;
use App\Models\Lab;
use App\Models\User;
use App\Models\UserBadge;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HackerController extends Controller
{
    private function getPortNumberFromContainer($containerName)
    {
        $command = "docker inspect --format=\"{{(index (index .NetworkSettings.Ports \\\"80/tcp\\\") 0).HostPort}}\" $containerName 2>&1";
        exec($command, $output, $exitCode);
        if ($exitCode === 0) {
            return (int)$output[0];
        }else {
            // Save the error output to a file for debugging
            $errorLogFile = storage_path("logs/docker-errors.log");
            file_put_contents($errorLogFile, "Error executing Docker inspect command:\n");
            file_put_contents($errorLogFile, $command . "\n", FILE_APPEND);
            file_put_contents($errorLogFile, implode("\n", $output) . "\n", FILE_APPEND);
    
            return null; // Port number not found
        }
    }
    public function runSqliForUser(Request $request)
    {
        $user_id=Auth::id();
        $lab_id=$request->lab_id;
        $project_name = "mutillidae_sqli_{$user_id}";
    
        $userDockerDir = storage_path("mutillidae-docker-master/www-sqli/user-instances/$user_id");
        if (!file_exists($userDockerDir)) {
            mkdir($userDockerDir, 0755, true);
        }

        $dockerComposeFile = "$userDockerDir/docker-compose.yml";
        $randomFlag = Str::random(20);

        $dockerComposeContent = "
        # Documentation: https://github.com/compose-spec/compose-spec/blob/master/spec.md
        # Purpose: Build local containers for the Mutillidae environment
        
        version: '3.7'
        services:
        
          database:
            container_name: database-sqli-$user_id
            image: webpwnized/mutillidae:database
            build: 
                context: ../../../database
                dockerfile: Dockerfile
            networks:
              - datanet   
        
          database_admin:
            container_name: database_admin-sqli-$user_id
            depends_on:
              - database
            image: webpwnized/mutillidae:database_admin
            build:
                context: ../../../database_admin
                dockerfile: Dockerfile
            ports:
              - 0.0.0.0::80
            networks:
              - datanet   
              
          www-sqli:
              container_name: www-sqli-$user_id
              depends_on:
                - database
              image: webpwnized/mutillidae:www-sqli
              build:
                  context: ../../../www-sqli
                  dockerfile: Dockerfile
              ports:
                - 0.0.0.0::80
                - 0.0.0.0::443
              networks:
                - datanet
              environment:
              - FLAG= flag-{$randomFlag}
          
        # Create network segments for the containers to use
        networks:
            datanet:
        ";

        file_put_contents($dockerComposeFile,$dockerComposeContent);
        //build command
        $command = "docker-compose -f $dockerComposeFile -p $project_name up -d 2>&1";
        exec($command, $output, $exitCode);

        
        // Check the exit code to determine if the command was successful
        if ($exitCode === 0) {
            $containerName="www-sqli-$user_id";
            $portNumber = $this->getPortNumberFromContainer($containerName);

            //Add active lab
            $active = ActiveLab::create([
                'user_id' => Auth::id(),
                'lab_id' => $lab_id,
                'flag' => $randomFlag,
                'project_name' => $project_name,
                'port' => $portNumber
            ]);

            $activeWithoutFlag =ActiveLab::select(['id', 'user_id', 'lab_id', 'project_name', 'port','launch_time'])->find($active->id);
            return response()->json([
                'message' => "Instance started for user ID {$user_id}",
                'port_number' => $portNumber,
                'active_lab' => $activeWithoutFlag,
                'output' => $output,
            ],200);
        } else {
            return response()->json([
                'message' => "Error starting instance for user ID {$user_id}",
                'output' => $output,
            ],500);
        }
    }
    public function runCommandInjection(Request $request)
    {
        $user_id=Auth::id();
        $lab_id=$request->lab_id;
        $project_name = "mutillidae_ci_{$user_id}";
    
        $userDockerDir = storage_path("mutillidae-docker-master/www-ci/user-instances/$user_id");
        if (!file_exists($userDockerDir)) {
            mkdir($userDockerDir, 0755, true);
        }

        $dockerComposeFile = "$userDockerDir/docker-compose.yml";
        $randomFlag = Str::random(20);

        $dockerComposeContent = "
        # Documentation: https://github.com/compose-spec/compose-spec/blob/master/spec.md
        # Purpose: Build local containers for the Mutillidae environment
        
        version: '3.7'
        services:
        
          database:
            container_name: database-ci-$user_id
            image: webpwnized/mutillidae:database
            build: 
                context: ../../../database
                dockerfile: Dockerfile
            networks:
              - datanet   
        
          database_admin:
            container_name: database_admin-ci-$user_id
            depends_on:
              - database
            image: webpwnized/mutillidae:database_admin
            build:
                context: ../../../database_admin
                dockerfile: Dockerfile
            ports:
              - 0.0.0.0::80
            networks:
              - datanet   
              
          www-ci:
              container_name: www-ci-$user_id
              depends_on:
                - database
              image: webpwnized/mutillidae:www-ci
              build:
                  context: ../../../www-ci
                  dockerfile: Dockerfile
              ports:
                - 0.0.0.0::80
                - 0.0.0.0::443
              networks:
                - datanet
              environment:
              - FLAG= flag-{$randomFlag}
          
        # Create network segments for the containers to use
        networks:
            datanet:
        ";

        file_put_contents($dockerComposeFile,$dockerComposeContent);
        //build command
        $command = "docker-compose -f $dockerComposeFile -p $project_name up -d 2>&1";
        exec($command, $output, $exitCode);

        
        // Check the exit code to determine if the command was successful
        if ($exitCode === 0) {
            $containerName="www-ci-$user_id";
            $portNumber = $this->getPortNumberFromContainer($containerName);

            //Add active lab
            $active = ActiveLab::create([
                'user_id' => Auth::id(),
                'lab_id' => $lab_id,
                'flag' => $randomFlag,
                'project_name' => $project_name,
                'port' => $portNumber
            ]);

            $activeWithoutFlag =ActiveLab::select(['id', 'user_id', 'lab_id', 'project_name', 'port','launch_time'])->find($active->id);
            return response()->json([
                'message' => "Instance started for user ID {$user_id}",
                'port_number' => $portNumber,
                'active_lab' => $activeWithoutFlag,
                'output' => $output,
            ],200);
        } else {
            return response()->json([
                'message' => "Error starting instance for user ID {$user_id}",
                'output' => $output,
            ],500);
        }
    }

    public function runInsecureJWT(Request $request)
    {
        $user_id=Auth::id();
        $lab_id=$request->lab_id;
        $project_name = "mutillidae_jwt_{$user_id}";
    
        $userDockerDir = storage_path("mutillidae-docker-master/www-jwt/user-instances/$user_id");
        if (!file_exists($userDockerDir)) {
            mkdir($userDockerDir, 0755, true);
        }

        $dockerComposeFile = "$userDockerDir/docker-compose.yml";
        $randomFlag = Str::random(20);

        $dockerComposeContent = "
        # Documentation: https://github.com/compose-spec/compose-spec/blob/master/spec.md
        # Purpose: Build local containers for the Mutillidae environment
        
        version: '3.7'
        services:
        
          database:
            container_name: database-jwt-$user_id
            image: webpwnized/mutillidae:database
            build: 
                context: ../../../database
                dockerfile: Dockerfile
            networks:
              - datanet   
        
          database_admin:
            container_name: database_admin-jwt-$user_id
            depends_on:
              - database
            image: webpwnized/mutillidae:database_admin
            build:
                context: ../../../database_admin
                dockerfile: Dockerfile
            ports:
              - 0.0.0.0::80
            networks:
              - datanet   
              
          www-jwt:
              container_name: www-jwt-$user_id
              depends_on:
                - database
              image: webpwnized/mutillidae:www-jwt
              build:
                  context: ../../../www-jwt
                  dockerfile: Dockerfile
              ports:
                - 0.0.0.0::80
                - 0.0.0.0::443
              networks:
                - datanet
              environment:
              - FLAG= flag-{$randomFlag}
          
        # Create network segments for the containers to use
        networks:
            datanet:
        ";

        file_put_contents($dockerComposeFile,$dockerComposeContent);
        //build command
        $command = "docker-compose -f $dockerComposeFile -p $project_name up -d 2>&1";
        exec($command, $output, $exitCode);

        
        // Check the exit code to determine if the command was successful
        if ($exitCode === 0) {
            $containerName="www-jwt-$user_id";
            $portNumber = $this->getPortNumberFromContainer($containerName);

            //Add active lab
            $active = ActiveLab::create([
                'user_id' => Auth::id(),
                'lab_id' => $lab_id,
                'flag' => $randomFlag,
                'project_name' => $project_name,
                'port' => $portNumber
            ]);

            $activeWithoutFlag =ActiveLab::select(['id', 'user_id', 'lab_id', 'project_name', 'port','launch_time'])->find($active->id);
            return response()->json([
                'message' => "Instance started for user ID {$user_id}",
                'port_number' => $portNumber,
                'active_lab' => $activeWithoutFlag,
                'output' => $output,
            ],200);
        } else {
            return response()->json([
                'message' => "Error starting instance for user ID {$user_id}",
                'output' => $output,
            ],500);
        }
    }
    public function stopUserLab($project_name)
    {
        $user_id = Auth::id();

        // Path to the user's Docker Compose file
        if($project_name=='mutillidae_sqli')
        {
            $userDockerDir = storage_path("mutillidae-docker-master/www-sqli/user-instances/$user_id");

        } else if($project_name=='mutillidae_ci'){
            $userDockerDir = storage_path("mutillidae-docker-master/www-ci/user-instances/$user_id");
        } else if($project_name=='mutillidae_jwt'){
            $userDockerDir = storage_path("mutillidae-docker-master/www-jwt/user-instances/$user_id");
        }
        $project_name = "{$project_name}_{$user_id}";

        $dockerComposeFile = "$userDockerDir/docker-compose.yml";
    
        // Stop and remove containers using docker-compose
        $command = "docker-compose -f $dockerComposeFile -p $project_name down 2>&1";
        exec($command, $output, $exitCode);
    
        // Check the exit code to determine if the command was successful
        if ($exitCode === 0) {
            // Find the associated ActiveLab instance by project_name
            $activeLab = ActiveLab::where('project_name', $project_name)->first();
    
            if (!$activeLab) {
                return response()->json([
                    'message' => 'Lab instance not found'
                ], 404);
            }
    
            // Delete the found ActiveLab instance
            if ($activeLab->delete()) {
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
    public function getLabs(){
        try{
    
            $labs = Lab::with('difficultyInfo')->paginate(9);
    
            foreach ($labs as $lab) {
                $lab->isActive = ActiveLab::where([['user_id','=',Auth::id()],['lab_id','=',$lab->id]])
                    ->exists();
                $lab->isComplete = CompletedLab::where([['user_id','=',Auth::id()],['lab_id','=',$lab->id]])
                ->exists();
                $lab->active_lab = ActiveLab::where([['user_id','=',Auth::id()],['lab_id','=',$lab->id]])
                ->first();
            }
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
    public function getActiveLabs()
    {
        try {
            $user_id = Auth::id();
            $active_labs = ActiveLab::where("user_id", $user_id)
            ->get(['id','lab_id', 'project_name', 'launch_time' ,'port']);
            
            $active_labs->load(['activeLabInfo.difficultyInfo']);
            if ($active_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No active labs'
                ], 204);
            } else {
                $active_labs = $active_labs->map(function ($lab) {
                    $difficultyInfo = $lab->activeLabInfo->difficultyInfo;

                    $lab->category_id = $lab->activeLabInfo->category_id;
                    $lab->difficulty_id = $lab->activeLabInfo->difficulty_id;
                    $lab->name = $lab->activeLabInfo->name;
                    $lab->objective = $lab->activeLabInfo->objective;
                    $lab->reward = $lab->activeLabInfo->reward;
                    $lab->icon_url = $lab->activeLabInfo->icon_url;

                    $lab->difficulty_info = $difficultyInfo;

                    unset($lab->activeLabInfo); // Remove the activeLabInfo key
                    return $lab;
                });
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
    
    public function getCompletedLabs()
    {
        try {
            $user_id = Auth::id();
            $completed_labs = CompletedLab::where("user_id", $user_id)
                ->get(['id', 'lab_id', 'complete_time']);
    
            // Explicitly load the 'completedLabInfo' relationship
            $completed_labs->load('completedLabInfo.difficultyInfo');
    
            if ($completed_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No completed labs'
                ], 204);
            } else {
                $completed_labs = $completed_labs->map(function ($lab) {
                    if ($lab->completedLabInfo) {
                        $difficultyInfo = $lab->completedLabInfo->difficultyInfo;

                        $lab->category_id = $lab->completedLabInfo->category_id;
                        $lab->difficulty_id = $lab->completedLabInfo->difficulty_id;
                        $lab->name = $lab->completedLabInfo->name;
                        $lab->objective = $lab->completedLabInfo->objective;
                        $lab->reward = $lab->completedLabInfo->reward;
                        $lab->icon_url = $lab->completedLabInfo->icon_url;
                        $lab->difficulty_info = $difficultyInfo;
                        unset($lab->completedLabInfo);
                        return $lab;
                    }
    
                    unset($lab->completedLabInfo);
                    return $lab;
                });
                return response()->json([
                    'message' => 'Completed labs found',
                    "completed_labs" => $completed_labs
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function submitFlag(Request $request)
    {
        try {
            $user_id = Auth::id();
            $user = Auth::user();
            $submitted_flag = $request->flag;
            $id = $request->id;
            
            $project_name=$request->project_name;

            if($project_name=="mutillidae_sqli_$user_id")
            {
                $userDockerDir = storage_path("mutillidae-docker-master/www-sqli/user-instances/$user_id");
    
            } else if($project_name=="mutillidae_ci_$user_id"){
                $userDockerDir = storage_path("mutillidae-docker-master/www-ci/user-instances/$user_id");
            } else if($project_name=="mutillidae_jwt_$user_id"){
                $userDockerDir = storage_path("mutillidae-docker-master/www-jwt/user-instances/$user_id");
            }
            $dockerComposeFile = "$userDockerDir/docker-compose.yml";
            
            $active_lab = ActiveLab::where([
                ["lab_id", '=', $id],
                ["user_id", '=', $user_id]
            ])->first();
            
            if (!$active_lab) {
                return response()->json([
                    'message' => 'Active lab not found'
                ], 404);
            } else {
                if($active_lab->flag !== $submitted_flag){
                    return response()->json([
                        'message' => 'Flag incorrect'
                    ], 404);
                }
                
                $lab = Lab::find($id);
                if (!$lab) {
                    return response()->json([
                        'message' => 'Lab not found'
                    ], 404);
                }
                
                $project_name=$active_lab->project_name;
                $command = "docker-compose -f $dockerComposeFile -p $project_name down 2>&1";
                exec($command, $output, $exitCode);

                if ($exitCode !== 0) {
                    return response()->json([
                        'message' => "Error stopping lab instance for user ID {$user_id}",
                        'output' => $output,
                    ]);
                }
                if(!$active_lab->delete()){
                    return response()->json([
                        'message' => "Error deleting active lab",
                        'output' => $output,
                    ]);
                }
                if (CompletedLab::where([["user_id",$user_id],['lab_id',$id]])->first()){
                    return response()->json([
                        'message' => 'Flag is correct, lab already completed before',
                        'completed_lab' => $lab
                    ], 200);
                }

                CompletedLab::create([
                    'user_id' => Auth::id(),
                    'lab_id' => $id
                ]);

                $new_reward = $user->rewards + $lab->reward; 
                $user->update(['rewards' => $new_reward]);

                $badge = Badge::where('lab_id',$id)->first();
                if($badge){
                    $user_badge=UserBadge::create([
                        'user_id' => $user_id,
                        'badge_id' => $badge->id
                    ]);
                }


                return response()->json([
                    'message' => 'Flag is correct',
                    'user_badge'=> $badge,
                    'completed_lab' => $lab
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getMyBadges()
{
    try {
        $user_id = Auth::id();

        $paginated_badges = UserBadge::select(['id', 'badge_id'])->where('user_id', $user_id)->with('badgeInfo')->paginate(4);
        if ($paginated_badges->isEmpty()) {
            return response()->json([
                'message' => 'No badges exist'
            ], 204);
        } else {
            $badges = $paginated_badges->items();
            foreach ($badges as $badge) {
                if ($badge->badgeInfo) {
                    $badge->category_id = $badge->badgeInfo->category_id;
                    $badge->name = $badge->badgeInfo->name;
                    $badge->icon_url = $badge->badgeInfo->icon_url;
                    unset($badge->badgeInfo);
                } else {
                    $badge->category_id = null;
                    $badge->name = null;
                    $badge->icon_url = null;
                }            
            }

            return response()->json([
                'message' => "Badges found.",
                'badges' => $paginated_badges
            ], 200);
        }
    } catch (Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}

public function searchBadges(Request $request)
{
    try {
        $user_id = Auth::id();
        $search_query=$request->input('query');
        if (empty($search_query)) {
            return response()->json([
                'message' => 'Search query is empty.'
            ], 400);
        }
        $paginated_badges = UserBadge::select(['id', 'badge_id'])
        ->where('user_id', $user_id)
        ->whereHas('badgeInfo', function ($query) use ($search_query) {
            $query->where('name', 'like', '%' . $search_query . '%');
        })
        ->with('badgeInfo')
        ->paginate(4);

        if ($paginated_badges->isEmpty()) {
            return response()->json([
                'message' => 'No badges exist'
            ], 204);
        } else {
            $badges = $paginated_badges->items();
            foreach ($badges as $badge) {
                if ($badge->badgeInfo) {
                    $badge->category_id = $badge->badgeInfo->category_id;
                    $badge->name = $badge->badgeInfo->name;
                    $badge->icon_url = $badge->badgeInfo->icon_url;
                    unset($badge->badgeInfo);
                } else {
                    $badge->category_id = null;
                    $badge->name = null;
                    $badge->icon_url = null;
                }            
            }

            return response()->json([
                'message' => "Badges found.",
                'badges' => $paginated_badges
            ], 200);
        }
    } catch (Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}
    public function statistics(){
        try{
            $user=Auth::user();
            $completed_lab_count = $user->completedLabs->count();
            $rank = $user->rank();
            $badge_count = $user->badges->count();
            return response()->json([
                "message" => 'Statistics created',
                'rewards' => $user->rewards,
                'rank' => $rank,
                'completed_labs' => $completed_lab_count,
                'badges' => $badge_count
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

public function modifyProfile(Request $request)
{
    try {
        $user = Auth::user();
        $changes = [];

        if ($request->has('name')) {
            $name = $request->input('name');
            if (!empty($name)) {
                $user->name = $name;
                $changes[] = 'name';
            }
        }

        if ($request->has('old_password') && $request->has('new_password')) {
            if (Hash::check($request->input('old_password'), $user->password)) {
                $user->password = Hash::make($request->input('new_password'));
                $changes[] = 'password';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Old password is incorrect.',
                ], 400);
            }
        }

        if ($request->has('profile_image')) {
            $base64Image = $request->input('profile_image');
            if (!empty($base64Image)) {
                $user=Auth::user();

                $base64Image = $request->input('profile_image');
                $binaryData = base64_decode($base64Image);
    
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $binaryData);
                finfo_close($finfo);
                $allowedMimeTypes = ['image/jpeg','image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
                $maxFileSize = 2048;
        
                if (!in_array($mimeType, $allowedMimeTypes) || strlen($binaryData) > ($maxFileSize * 1024)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid image file.',
                    ], 400);
                }
        
    
                $fileExtension = explode('/', $mimeType)[1]; 
    
                $fileName = uniqid() . '.' . $fileExtension;
        
                Storage::disk('public')->put('profiles/' . $fileName, $binaryData);
                $publicUrl = Storage::disk('public')->url('profiles/' . $fileName);
                $user->profile_url = $publicUrl;
    
                $changes[] = 'profile_url';   
            }
        }

        if ($user->save()) {
            return response()->json([
                'message' => 'Profile updated successfully',
                'changes' => $changes,
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to update profile',
            ], 500);
        }
    } catch (Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}

}



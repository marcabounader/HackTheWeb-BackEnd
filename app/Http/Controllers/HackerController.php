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
        
        $userDockerDir = storage_path("mutillidae-docker-master/user-instances/$user_id");
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
            container_name: database-$user_id
            image: webpwnized/mutillidae:database
            build: 
                context: ./database
                dockerfile: Dockerfile
            networks:
              - datanet   
            stop_grace_period: 1m
        
          database_admin:
            container_name: database_admin-$user_id
            depends_on:
              - database
            image: webpwnized/mutillidae:database_admin
            build:
                context: ./database_admin
                dockerfile: Dockerfile
            ports:
              - 127.0.0.1::80
            networks:
              - datanet   
            stop_grace_period: 1m
        
          directory:
            container_name: directory-$user_id
            image: webpwnized/mutillidae:ldap
            build:
                context: ./ldap
                dockerfile: Dockerfile
            volumes:
              - ldap_data:/var/lib/ldap
              - ldap_config:/etc/ldap/slapd.d
            ports:
              - 127.0.0.1::389
            networks:
              - ldapnet
            stop_grace_period: 1h
        
          directory_admin:
            container_name: directory_admin-$user_id
            depends_on:
              - directory
            image: webpwnized/mutillidae:ldap_admin
            build:
                context: ./ldap_admin          
                dockerfile: Dockerfile
            ports:
              - 127.0.0.1::80
            networks:
              - ldapnet
            stop_grace_period: 1h
              
          www-sqli:
              container_name: www-sqli-$user_id
              depends_on:
                - database
                - directory
              image: webpwnized/mutillidae:www-sqli
              build:
                  context: ../../../../storage/mutillidae-docker-master/www-sqli
                  dockerfile: Dockerfile
              ports:
                - 127.0.0.1::80
                - 127.0.0.1::443
              networks:
                - datanet
                - ldapnet
              environment:
              - FLAG= flag-{$randomFlag}
              stop_grace_period: 1h
        # Volumes to persist data used by the LDAP server
        volumes:
          ldap_data:
          ldap_config:
          
        # Create network segments for the containers to use
        networks:
            datanet:
            ldapnet:        
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

            return response()->json([
                'message' => "Instance started for user ID {$user_id}",
                'port_number' => $portNumber,
                'active_lab' => $active,
                'output' => $output, // Capture the command output
            ]);
        } else {
            // The command encountered an error
            return response()->json([
                'message' => "Error starting instance for user ID {$user_id}",
                'output' => $output, // Capture the command output
            ]);
        }
    }
    
    public function stopUserLab($project_name)
    {
        $user_id = Auth::id();
        $project_name = "{$project_name}_{$user_id}";
        // Path to the user's Docker Compose file
        $userDockerDir = storage_path("mutillidae-docker-master/user-instances/$user_id");
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

    public function getActiveLabs()
    {
        try {
            $user_id = Auth::id();
            $active_labs = ActiveLab::where("user_id", $user_id)
            ->get(['id','lab_id', 'project_name', 'launch_time' ,'port']);
            
            $active_labs->load('activeLabInfo');

            if ($active_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No active labs'
                ], 404);
            } else {
                $active_labs = $active_labs->map(function ($lab) {
                    $lab->category_id = $lab->activeLabInfo->category_id;
                    $lab->difficulty_id = $lab->activeLabInfo->difficulty_id;
                    $lab->name = $lab->activeLabInfo->name;
                    $lab->objective = $lab->activeLabInfo->objective;
                    $lab->rewards = $lab->activeLabInfo->rewards;
                    $lab->icon_url = $lab->activeLabInfo->icon_url;
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
            $completed_labs->load('completedLabInfo');
    
            if ($completed_labs->isEmpty()) {
                return response()->json([
                    'message' => 'No completed labs'
                ], 404);
            } else {
                $completed_labs = $completed_labs->map(function ($lab) {
                    if ($lab->completedLabInfo) {
                        $lab->category_id = $lab->completedLabInfo->category_id;
                        $lab->difficulty_id = $lab->completedLabInfo->difficulty_id;
                        $lab->name = $lab->completedLabInfo->name;
                        $lab->objective = $lab->completedLabInfo->objective;
                        $lab->rewards = $lab->completedLabInfo->rewards;
                        $lab->icon_url = $lab->completedLabInfo->icon_url;
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
            $userDockerDir = storage_path("mutillidae-docker-master/user-instances/$user_id");
            $dockerComposeFile = "$userDockerDir/docker-compose.yml";
            
            $active_lab = ActiveLab::where([
                ["lab_id", '=', $id],
                ["flag", '=', $submitted_flag],
                ["user_id", '=', $user_id]
            ])->first();
            
            if (!$active_lab) {
                return response()->json([
                    'message' => 'Active lab not found'
                ], 404);
            } else {

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
                $badge = Badge::where('name','SQLi beginner')->first();
                
                $user_badge=UserBadge::create([
                    'user_id' => $user_id,
                    'badge_id' => $badge->id
                ]);

                return response()->json([
                    'message' => 'Flag is correct',
                    'user_badge'=>$badge,
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

        $badges = UserBadge::where('user_id', $user_id)->with('badgeInfo')->get(['id', 'badge_id']);

        if ($badges->isEmpty()) {
            return response()->json([
                'message' => 'No badges exist'
            ], 404);
        } else {
            $badges = $badges->map(function ($badge) {
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
                return $badge;
            });

            return response()->json([
                'message' => "Badges found.",
                'badges' => $badges
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

        // Check if 'name' is in the request and update the name
        if ($request->has('name')) {
            $user->name = $request->input('name');
            $changes[] = 'name';
        }

        // Check if 'old_password' and 'new_password' are in the request
        if ($request->has('old_password') && $request->has('new_password')) {
            // Verify old_password matches the one in the database
            if (Hash::check($request->input('old_password'), $user->password)) {
                // Update the password to new_password
                $user->password = Hash::make($request->input('new_password'));
                $changes[] = 'password';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Old password is incorrect.',
                ], 400);
            }
        }

        // Check if 'profile_image' is in the request and update the profile_url
        if ($request->has('profile_image')) {
            $user=Auth::user();

            $base64Image = $request->input('profile_image');
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
    
            Storage::disk('public')->put('profiles/' . $fileName, $binaryData);
            $publicUrl = Storage::disk('public')->url('profiles/' . $fileName);
            $user->profile_url = $publicUrl;

            $changes[] = 'profile_url';
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

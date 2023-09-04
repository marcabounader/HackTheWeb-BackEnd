<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DockerController extends Controller
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
    public function runInstanceForUser(Request $request)
    {
        $user_id=Auth::id();

        $projectName = "mutillidae_user_{$user_id}";

        $userDockerDir = storage_path("mutillidae-docker-master/user-instances/$user_id");
        if (!file_exists($userDockerDir)) {
            mkdir($userDockerDir, 0755, true);
        }

        $dockerComposeFile = "$userDockerDir/docker-compose.yml";

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
        
          www:
            container_name: www-$user_id
            depends_on:
              - database
              - directory
            image: webpwnized/mutillidae:www
            build:
                context: ./www
                dockerfile: Dockerfile
            ports:
              - 127.0.0.1::80
              - 127.0.0.1::443
            networks:
              - datanet
              - ldapnet
            environment:
              - SECRET_KEY=marc
        
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
              
          www2:
              container_name: www-2-$user_id
              depends_on:
                - database
                - directory
              image: webpwnized/mutillidae
              build:
                  context: ../../../../storage/mutillidae-docker-master/www2
                  dockerfile: Dockerfile
              ports:
                - 127.0.0.1::80
                - 127.0.0.1::443
              networks:
                - datanet
                - ldapnet
              environment:
              - FLAG=marc
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
        $command = "docker-compose -f $dockerComposeFile -p $projectName up -d 2>&1";
        exec($command, $output, $exitCode);

        
        // Check the exit code to determine if the command was successful
        if ($exitCode === 0) {
            $containerName="www-$user_id";
            $portNumber = $this->getPortNumberFromContainer($containerName);

    
            return response()->json([
                'message' => "Instance started for user ID {$user_id}",
                'port_number' => $portNumber,
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

}
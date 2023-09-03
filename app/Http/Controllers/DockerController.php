<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DockerController extends Controller
{
    public function runCommand(Request $request)
    {
        $command = $request->input('command'); 
    
        exec($command, $output, $exitCode);
    
        if ($exitCode === 0) {
            return response()->json(['output' => $output]);
        } else {
            return response()->json(['error' => 'Docker command failed'], 500);
        }
    }
}

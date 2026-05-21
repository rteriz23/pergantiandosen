<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    /**
     * Handle incoming deployment webhook requests from GitHub.
     */
    public function deploy(Request $request)
    {
        Log::info('Deploy Webhook: Deployment triggered.');

        // 1. Security Check: Validate Secret Token
        $token = env('DEPLOY_TOKEN');
        
        if (empty($token)) {
            Log::error('Deploy Webhook: DEPLOY_TOKEN is not set in the server env.');
            return response()->json([
                'status' => 'error',
                'message' => 'Deployment is not configured on this server.'
            ], 500);
        }

        // Method A: Check via HTTP Authorization Header (Bearer)
        $authHeader = $request->header('Authorization');
        $bearerToken = $authHeader ? str_replace('Bearer ', '', $authHeader) : null;

        // Method B: Check via GitHub Webhook Signature (HMAC SHA-256)
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        $isSignatureValid = false;

        if ($signature && $payload) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $token);
            $isSignatureValid = hash_equals($expectedSignature, $signature);
        }

        // Validate using either method
        if (($bearerToken !== $token) && !$isSignatureValid) {
            Log::warning('Deploy Webhook: Unauthorized deployment attempt.', [
                'ip' => $request->ip(),
                'has_signature' => !empty($signature),
                'has_bearer' => !empty($bearerToken),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 401);
        }

        // 2. Run Deployment Commands
        Log::info('Deploy Webhook: Authentication successful. Executing commands...');
        
        $basePath = base_path();
        $output = [];
        $status = 0;

        // Commands to run
        $commands = [
            'echo "Deployment started at $(date)"',
            "cd $basePath",
            'git fetch origin main',
            'git reset --hard origin/main',
            'composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader',
            'php artisan migrate --force',
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache',
            'php artisan up',
            'echo "Deployment finished at $(date)"'
        ];

        // Execute commands sequentially
        foreach ($commands as $command) {
            Log::info("Deploy Webhook: Executing: $command");
            $cmdOutput = shell_exec("$command 2>&1");
            $output[] = [
                'command' => $command,
                'output' => trim($cmdOutput)
            ];
        }

        Log::info('Deploy Webhook: Deployment process finished.');

        return response()->json([
            'status' => 'success',
            'message' => 'Deployment executed successfully.',
            'details' => $output
        ]);
    }
}

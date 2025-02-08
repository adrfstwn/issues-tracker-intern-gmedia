<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Webhooks;
use App\Jobs\UpdateIssue;
use App\Jobs\CreatedIssue;
use App\Jobs\UpdateSlug;
use App\Jobs\UpdateUserOpenProject;

class WebhookController extends Controller
{
    private $sentryApiSecret;

    public function __construct()
    {
        $this->sentryApiSecret = env('SENTRY_WEBHOOK_TOKEN');
    }

    public function handleWebhook(Request $request): Response
    {
        $secret = $this->sentryApiSecret;

        if (!$this->verifySignature($request, $secret)) {
            Log::error('Invalid signature');
            return response('bad signature', 401);
        }

        $response = response('success get issue', 200);

        dispatch(function () use ($request){
            $payload = $request->all();
            $resource = $request->header('sentry-hook-resource');
            $action = $request->input('action');

            if ($resource == 'issue') {
                if ($action == 'created') {

                    $issueID = $request->input('data.issue.id');
                    Log::info('Issue Created', ['issueID' => $issueID]);

                    // Jalankan Jobs Update Slug
                    try {

                        UpdateSlug::dispatch();
                        Log::info('Running Update Slug Job...');
                    } catch (\Exception $e) {
                        Log::error('Error dispatching job UpdateSlug: ' . $e->getMessage());
                    }

                    // Jalankan Jobs Create Issue
                    try {

                        CreatedIssue::dispatch($issueID);
                        Log::info('Running Create Issue Job...');
                    } catch (\Exception $e) {
                        Log::error('Error dispatching job CreateProject: ' . $e->getMessage());
                    }

                } elseif ($action == 'ignored' || $action == 'resolved' || $action == 'assigned' ) {

                    $issueID = $request->input('data.issue.id');
                    Log::info("Issue {$action}", ['issueID' => $issueID]);

                    // Jalankan Jobs
                    try {
                        UpdateIssue::dispatch($issueID);
                        Log::info('Running Update Issue Job...');
                    } catch (\Exception $e) {
                        Log::error('Error dispatching job UpdateProject: ' . $e->getMessage());
                    }

                } else {

                    Log::info('Unhandled action for issue', ['action' => $action]);
                }
            }

            
            Webhooks::create(['payload' => $payload]);
            
            try{
                UpdateUserOpenProject::dispatch();
                Log::info('Running Update User Open Project Job...');
            }catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            
        });

        return $response;
    }

    private function verifySignature($request, $secret): bool
    {
        $body = $request->getContent(); 

        Log::info('Received raw body', ['body' => $body]);

        $calculatedHmac = hash_hmac('sha256', $body, $secret);

        $receivedHmac = $request->header('sentry-hook-signature');

        Log::info('Verifying signature', [
            'received_hmac' => $receivedHmac,
            'calculated_hmac' => $calculatedHmac,
        ]);

        if (hash_equals($calculatedHmac, $receivedHmac)) {
            Log::info('Signature verification passed!!');
            return true;
        }

        Log::error('Signature verification failed');
        return false;
    }
}


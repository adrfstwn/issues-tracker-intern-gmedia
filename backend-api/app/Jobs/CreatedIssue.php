<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Issues;
use App\Models\StackTrace;
use App\Models\Events;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CreatedIssue implements ShouldQueue
{
    use Queueable;
    private $token;
    private $baseUrl;
    private $client;
    private int $issueId;

    public function __construct(int $issueId)
    {
        $this->token = env('SENTRY_API_TOKEN');
        $this->baseUrl = 'https://sentry.varx.ai/api/0/';
        $this->issueId = $issueId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        $this->client = new Client();

        try {
            // Fetch issue details from the Sentry API
            $response = $this->client->request("GET", "{$this->baseUrl}issues/{$this->issueId}/", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $issueData = json_decode($response->getBody()->getContents(), true);

            if (!isset($issueData['id'])) {
                throw new \Exception("Invalid issue data received.");
            }

            $projectSlug = $issueData['project']['slug'];
            
            Issues::create([
                'project_slug' => $projectSlug,
                'issues_id' => $this->issueId,
                'issues_json' => $issueData
            ]);

            $this->CreateEvents($this->issueId);
            $this->CreateStackTrace($this->issueId);

            $endTime = microtime(true);
            Log::info("Issue {$this->issueId} Created Succesfully!! " . ($endTime - $startTime) . ' seconds');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }

    }

    private function CreateEvents($issueId)
    {
        try {
            $response = $this->client->request("GET", "{$this->baseUrl}issues/{$issueId}/events/", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $eventData = json_decode($response->getBody()->getContents(), true);

            if (!isset($eventData['id'])) {
                throw new \Exception("Invalid event data received.");
            }

            Events::create([
                'issues_id' => $issueId, 
                'events_id' => $eventData['id'],
                'events_json' => $eventData
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function CreateStackTrace($issueId){
        try {
            $response = $this->client->request("GET", "{$this->baseUrl}issues/{$issueId}/hashes/", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $stackTraceData = json_decode($response->getBody()->getContents(), true);

            if (!isset($stackTraceData['id'])) {
                throw new \Exception("Invalid hashes data received.");
            }

            StackTrace::create([
                'issues_id' => $issueId, 
                'stack_trace_id' => $stackTraceData['id'],
                'stack_trace_json' => $stackTraceData
            ]);
        } catch (\Exception $e) {
            Log::error( $e->getMessage());
        } 
    }
}

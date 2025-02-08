<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Issues;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class UpdateIssue implements ShouldQueue
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
            $response = $this->client->request('GET', "{$this->baseUrl}issues/{$this->issueId}/", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $issueData = json_decode($response->getBody()->getContents(), true);
            
            $issue = Issues::where('issues_id', $this->issueId)->first();

            if($issue){
                $issue->update([
                    'issues_json' => $issueData
                ]);
            }else{
                Log::error('Issue Not Found');
            }

            $endTime = microtime(true);
            Log::info("Issue {$this->issueId} Updated Succesfully!! " . ($endTime - $startTime) . ' seconds');
            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }

    }
}

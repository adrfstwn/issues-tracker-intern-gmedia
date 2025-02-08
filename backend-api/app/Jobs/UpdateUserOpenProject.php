<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\UserOpenProject;

class UpdateUserOpenProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private $tokenOpenProject;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->tokenOpenProject = env('OPENPROJECT_API_TOKEN');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $client = new Client();

        try {
            $response = $client->request('GET', "https://project.gmedia.id/api/v3/memberships", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode('apikey:' . $this->tokenOpenProject),
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $usersData = json_decode($response->getBody(), true);

            if (!isset($usersData['_embedded']['elements'])) {
                Log::warning('No membership data found in the response.');
                return;
            }

            foreach ($usersData['_embedded']['elements'] as $userData) {
                UserOpenProject::updateOrCreate(
                    ['memberships_id' => $userData['id']],
                    [
                        'openproject_name' => $userData['_links']['project']['title'] ?? null,
                        'user_href' => $userData['_links']['principal']['href'] ?? null,
                        'name' => $userData['_links']['principal']['title'] ?? null,
                    ]
                );
            }

            $endTime = microtime(true);
            Log::info("User Open Project updated successfully in " . ($endTime - $startTime) . " seconds");

        } catch (\Exception $e) {
            Log::error("Error updating User Open Project: " . $e->getMessage());
            throw $e;
        }
    }
}

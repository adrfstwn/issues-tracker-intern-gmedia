<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Project;

class UpdateSlug implements ShouldQueue
{
    use Queueable;
    private $token;
    private $baseUrl;
    private $client;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->token = env('SENTRY_API_TOKEN');
        $this->baseUrl = 'https://sentry.varx.ai/api/0/';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        $this->client = new Client();

        try {
            $response = $this->client->request('GET', "{$this->baseUrl}projects/", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $projects = json_decode($response->getBody()->getContents(), true);

            // Simpan data proyek ke database
            foreach ($projects as $project) {
                Project::updateOrCreate(
                    ['project_slug' => $project['slug']],
                    ['project_json' => $project]
                );
            }

            $endTime = microtime(true);
            Log::info('UpdateSlug job completed!! ' . ($endTime - $startTime) . ' seconds');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }

    }
}

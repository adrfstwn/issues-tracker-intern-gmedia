<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Issues;
use App\Models\Events;
use App\Models\StackTrace;
use App\Models\UserOpenProject;


class UpdateProject implements ShouldQueue
{
    use Queueable;
    private $token;
    private $baseUrl;
    private $client;

    public function __construct()
    {
        $this->token = env('SENTRY_API_TOKEN');
        $this->baseUrl = 'https://sentry.varx.ai/api/0/';
    }
    public function handle()
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
                $savedProject = Project::updateOrCreate(
                    ['project_slug' => $project['slug']],
                    ['project_json' => $project]
                );

                $this->updateIssues($project['slug']);
            }

            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        
        $this->updateUserOpenProject();
        $endTime = microtime(true);
        Log::info('UpdateProject job completed!! ' . ($endTime - $startTime) . ' seconds');

    }
    private function updateIssues($slug){
        $url = "{$this->baseUrl}projects/sentry/{$slug}/issues/?query=";

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $issues = json_decode($response->getBody()->getContents(), true);

            foreach ($issues as $issue) {
                $savedIssue = Issues::updateOrCreate(
                    ['project_slug' => $slug, 'issues_id' => $issue['id']],
                    ['issues_json' => $issue]
                );
                // Log::info("Issue {$issue['id']} untuk project {$slug} berhasil diperbarui di database.");

                $this->updateEvents($savedIssue->issues_id);
                $this->updateStackTrace($savedIssue->issues_id);   
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // Log::error("Gagal memperbarui issues untuk project {$slug}: " . $e->getMessage());
        }
    }
    private function updateEvents($issueId)
    {
        $url = "{$this->baseUrl}issues/{$issueId}/events/";

        try {
            $response = $this->client->request("GET", $url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $events = json_decode($response->getBody()->getContents(), true);

            foreach ($events as $event) {
                Events::updateOrCreate(
                    ['issues_id' => $issueId, 'events_id' => $event['id']],
                    ['events_json' => $event]
                );
            }

            // Log::info("Events untuk issue {$issueId} berhasil diperbarui di database.");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // Log::error("Gagal memperbarui events untuk issue {$issueId}: " . $e->getMessage());
        }
    }
    private function updateStackTrace($issueId){
        $url = "{$this->baseUrl}issues/{$issueId}/hashes/";

        try {
            $response = $this->client->request("GET", $url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $stackTraces = json_decode($response->getBody()->getContents(), true);

            foreach ($stackTraces as $stackTrace) {
                StackTrace::updateOrCreate(
                    ['issues_id' => $issueId, 'stack_trace_id' => $stackTrace['id']],
                    ['stack_trace_json' => $stackTrace]
                );
            }

            // Log::info("Stack Trace untuk issue {$issueId} berhasil diperbarui di database.");
        } catch (\Exception $e) {
            Log::error( $e->getMessage());
            // Log::error("Gagal memperbarui stack trace untuk issue {$issueId}: " . $e->getMessage());
        } 
    }
    private function updateUserOpenProject(){
        try {
            $response = $this->client->request('GET', "https://project.gmedia.id/api/v3/memberships", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode('apikey:' . env('OPENPROJECT_API_TOKEN')),
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            if ($response->getStatusCode() === 200) {
                $usersData = json_decode($response->getBody(), true);

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

                // Log::info("User fetched and saved successfully.");
            } else {
                Log::error("Failed updating user" . $response->getBody());
            }
        } catch (\Exception $e) {
            Log::error("Error updating User Open Project" . $e->getMessage());
        }
    }
}

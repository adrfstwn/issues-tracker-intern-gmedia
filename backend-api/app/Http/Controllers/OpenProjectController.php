<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Issues;
use App\Models\OpenProject;
use App\Models\Project;
use App\Models\UserOpenProject;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenProjectController extends Controller
{
    private $client;
    
    public function getOpenProjects(): JsonResponse
    {
        $openProjects = OpenProject::all();

        return response()->json([
            'status' => 'success',
            'data' => $openProjects->map(function ($open_project) {
                return [
                    'issues_id' => $open_project->issues_id,
                    'assignee_id'=>$open_project->assignee_id,
                    'assignee_name' => $open_project->assignee_name,
                    'project_id' => $open_project->project_id,
                    'project_name' => $open_project->project_name,
                ];
            }),
        ]);
    }
   public function getAssignees(Request $request): JsonResponse
    {
        $projectName = $request->query('openproject_name');
        $query = UserOpenProject::query();

        if ($projectName) {
            $query->where('openproject_name', $projectName);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No assignees found for the specified project.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $users->map(function ($user) {
                return [
                    'memberships_id' => $user->memberships_id,
                    'openproject_name' => $user->openproject_name,
                    'user_href' => $user->user_href,
                    'name' => $user->name,
                ];
            }),
        ]);
    }
    public function getProjects(): JsonResponse
    {
        $this->client = new Client();

        try {
            $response = $this->client->request('GET', "https://project.gmedia.id/api/v3/projects", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode('apikey:' . env('OPENPROJECT_API_TOKEN')),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $projectsData = json_decode($response->getBody(), true);

            if (!isset($projectsData['_embedded']['elements'])) {
                Log::error("Unexpected response structure from API.");
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unexpected response from the API.',
                ], 500);
            }

            $projects = collect($projectsData['_embedded']['elements'])->map(function ($projectData) {
                return [
                    'id' => $projectData['id'] ?? null,
                    'identifier' => $projectData['identifier'] ?? null,
                    'name' => $projectData['name'] ?? null,
                    'active' => $projectData['active'] ?? null,
                    'href' => $projectData['_links']['self']['href'] ?? null,
                ];
            });

            // Log::info("Projects fetched successfully.");

            return response()->json([
                'status' => 'success',
                'data' => $projects,
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching projects: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch projects.',
            ], 500);
        }
    }
    public function saveProjectData(Request $request, $slug): JsonResponse
    {
        $projectRecord = Project::where('project_slug', $slug)->first();

        if (!$projectRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project with the given slug was not found.',
            ], 404);
        }

        $validated = $request->validate([
            'openproject_id' => 'required|integer',
            'openproject_name' => 'required|string',
        ]);

        $projectRecord->update([
            'openproject_id' => $validated['openproject_id'],
            'openproject_name' => $validated['openproject_name'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Root project updated successfully.',
            'data' => $projectRecord->only(['id', 'project_slug', 'openproject_id', 'openproject_name']),
        ], 200);
    }
    public function postIssue(Request $request, $id): JsonResponse
    {
        $issue = Issues::where('issues_id', $id)->first();

        if (!$issue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issue not found',
            ], 404);
        }

        $projectSlug = $issue->issues_json['project']['slug'] ?? null;

        if (!$projectSlug) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project slug is missing in the issue data.',
            ], 400);
        }

        $project = Project::where('project_slug', $projectSlug)->first();

        if (!$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project with the given slug not found in the database.',
            ], 404);
        }

        $existingOpenProject = OpenProject::where('issues_id', $id)->first();

        if ($existingOpenProject) {
            return response()->json([
                'status' => 'error',
                'message' => 'This issue has already been posted to OpenProject.',
            ], 400);
        }

        $projectId = $project->openproject_id;
        $projectName = $project->openproject_name;
        $issueData = $issue->issues_json;

        $eventCount = $issueData['count'] ?? 0;
        $priorityHref = $eventCount > 100 ? '/api/v3/priorities/9' : '/api/v3/priorities/8'; // 9=high, 8=normal

        $request->validate([
            'assignee_id' => 'required|string',
        ]);

        $assigneeId = $request->input('assignee_id');
        $user = UserOpenProject::where('user_href', $assigneeId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => "User ID not found in OpenProject.",
            ], 404);
        }

        $assigneeLink = ['href' => $user->user_href];
        $assigneeName = $user->name;

        $data = [
            '_links' => [
                'project' => [
                    'href' => "/api/v3/projects/{$projectId}",
                ],
                'type' => [
                    'href' => '/api/v3/types/1', // 1=task
                ],
                'assignee' => $assigneeLink,
            ],
            'subject' => $issueData['title'],
            'description' => [
                'format' => 'markdown',
                'raw' => sprintf(
                    "Issues ID: %s\nProject: %s\nCulprit: %s",
                    $issueData['id'] ?? 'N/A',
                    $projectSlug,
                    $issueData['culprit'] ?? 'N/A'
                ),
            ],
            'status' => [
                'href' => '/api/v3/statuses/1', // 1=new
            ],
            'priority' => [
                'href' => $priorityHref,
            ],
            'startDate' => now()->toDateString(),
            'dueDate' => null,
        ];

        try {
            $response = Http::withBasicAuth('apikey', env('OPENPROJECT_API_TOKEN'))
                ->post('https://project.gmedia.id/api/v3/work_packages', $data);

            if ($response->successful()) {
                $responseData = $response->json();
                $workPackageId = $responseData['id'] ?? null;
                $lockVersion = $responseData['lockVersion'] ?? null;

                if (!$workPackageId || !$lockVersion) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to retrieve work package ID or lockVersion.',
                    ], 500);
                }

                OpenProject::updateOrCreate(
                    ['issues_id' => $issue->issues_id],
                    [
                        'data' => $data,
                        'assignee_id' => $assigneeId,
                        'assignee_name' => $assigneeName,
                        'project_id' => $projectId,
                        'project_name' => $projectName,
                        'work_package_id' => $workPackageId,
                        'lock_version' => $lockVersion,
                    ]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Work package successfully created in OpenProject.',
                    'data' => $responseData,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create work package in OpenProject.',
                    'details' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the work package.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateWorkPackage(Request $request, $id): JsonResponse
    {
        $request->validate([
            'assignee_id' => 'required|string',
        ]);

        $openProjectRecord = OpenProject::where('issues_id', $id)->first();

        if (!$openProjectRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'OpenProject record not found for the provided issues ID.',
            ], 404);
        }

        $workPackageId = $openProjectRecord->work_package_id;
        $lockVersion = $openProjectRecord->lock_version;

        if (!$lockVersion) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lock version is missing in the OpenProject table.',
            ], 400);
        }

        $assigneeId = $request->input('assignee_id');
        $user = UserOpenProject::where('user_href', $assigneeId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID not found in OpenProject.',
            ], 404);
        }

        // Prepare the payload for OpenProject API
        $assigneeLink = ['href' => $user->user_href];
        $data = [
            '_links' => [
                'assignee' => $assigneeLink,
            ],
            'lockVersion' => $lockVersion,
        ];

        try {
            $updateResponse = Http::withHeaders([
                'Accept' => 'application/hal+json',
                'Content-Type' => 'application/json',
            ])
            ->withBasicAuth('apikey', env('OPENPROJECT_API_TOKEN'))
            ->patch("https://project.gmedia.id/api/v3/work_packages/{$workPackageId}", $data);

            if ($updateResponse->successful()) {
                $newLockVersion = $updateResponse->json()['lockVersion'] ?? null;

                if ($newLockVersion) {
                    $openProjectRecord->update([
                        'lock_version' => $newLockVersion,
                        'assignee_id' => $assigneeId,
                        'assignee_name' => $user->name,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Work package successfully updated in OpenProject.',
                    'data' => $updateResponse->json(),
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update work package in OpenProject.',
                'details' => $updateResponse->json(),
            ], $updateResponse->status());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the work package.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

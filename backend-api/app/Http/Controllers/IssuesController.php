<?php

namespace App\Http\Controllers;

use App\Models\StackTrace;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Issues;
use App\Models\Events;

class IssuesController extends Controller
{
    protected $client;
    protected $token;
    protected $baseUrl = 'https://sentry.varx.ai/api/0/';

    public function __construct()
    {
        $this->client = new Client();
        $this->token = env('SENTRY_API_TOKEN');
    }

    public function showAllIssue(Request $request): JsonResponse
    {
        $slug = $request->input('slug');
        $status = $request->input('status');

        $query = Issues::with(['openProject', 'project.userOpenProject']); // Load the openProject and userOpenProjects relationships

        if ($slug) {
            $query->where('project_slug', $slug);
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No issues found in database.'
            ], 404);
        }

        $issuesCollection = collect();

        $projects->each(function ($project) use ($issuesCollection) {
            // Ambil nama dari relasi project
            $openProjectName = $project->project->openproject_name ?? null;

            // Ambil data assignee dari relasi openProject
            $assigneeName = $project->openProject->assignee_name ?? 'unassigned';
            $assigneeId = $project->openProject->assignee_id ?? null;

            // Periksa apakah userOpenProjects ada dan merupakan koleksi
            $userOpenProjects = $project->project->userOpenProject ?? [];

            $issues = $project->issues_json;
            // Pastikan ini adalah koleksi atau array

            if (is_array($issues)) {
                $issues['assignee_name'] = $assigneeName;
                $issues['assignee_id'] = $assigneeId;
                $issues['openProject'] = $openProjectName;

                // Mengisi data user dalam array
                $issues['data_user'] = [];

                foreach ($userOpenProjects as $userOpenProject) {
                    $openProjectUserId = $userOpenProject->memberships_id ?? null;
                    $openProjectUserName = $userOpenProject->name ?? null;
                    $openProjectUserHref = $userOpenProject->user_href ?? null;

                    $issues['data_user'][] = [
                        'openProjectUserId' => $openProjectUserId,
                        'openProjectUserName' => $openProjectUserName,
                        'openProjectUserHref' => $openProjectUserHref,
                    ];
                }

                $issuesCollection->push($issues);
            }

        });

        // Apply status filter
        if ($status) {
            $issuesCollection = $issuesCollection->filter(function ($issue) use ($status) {
                return $issue['status'] === $status;
            });
        }
    
        // Sort issues
        $sort = $request->input('sort', 'desc');
        $issuesCollection = $sort === 'asc'
            ? $issuesCollection->sortBy('lastSeen')->values()
            : $issuesCollection->sortByDesc('lastSeen')->values();

        // Paginate issues
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $issuesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedIssues = new LengthAwarePaginator(
            $currentPageItems,
            $issuesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json([
            'status' => 'success',
            'issues' => $paginatedIssues->map(function ($issue) {
                $statusOptions = [
                    'unresolved' => 'unresolved',
                    'resolved' => 'resolved',
                    'ignored' => 'ignored'
                ];

                return [
                    'id' => $issue['id'],
                    'shareId' => $issue['shareId'] ?? null,
                    'shortId' => $issue['shortId'],
                    'title' => $issue['title'],
                    'culprit' => $issue['culprit'],
                    'level' => $issue['level'],
                    'status' => [
                        'current' => $issue['status'],
                        'options' => $statusOptions,
                    ],
                    'platform' => $issue['platform'],
                    'project' => $issue['project'],
                    'type' => $issue['type'],
                    'metadata' => $issue['metadata'] ?? [],
                    'count' => $issue['count'],
                    'userCount' => $issue['userCount'],
                    'firstSeen' => $issue['firstSeen'],
                    'lastSeen' => $issue['lastSeen'],
                    'stats' => $issue['stats'] ?? [],
                    'assignees' => [
                        'assignee_id' => $issue['assignee_id'],
                        'assignee_name' => $issue['assignee_name'],
                    ],
                    'openProject' => $issue['openProject'],
                    'data_user' => $issue['data_user'],
                ];
            }),
            'pagination' => [
                'total' => $paginatedIssues->total(),
                'per_page' => $paginatedIssues->perPage(),
                'current_page' => $paginatedIssues->currentPage(),
                'last_page' => $paginatedIssues->lastPage(),
                'next_page_url' => $paginatedIssues->hasMorePages() ? $paginatedIssues->nextPageUrl() : null,
                'prev_page_url' => $paginatedIssues->onFirstPage() ? null : $paginatedIssues->previousPageUrl(),
            ],
        ], 200);
    }
    public function showIssueId($id): JsonResponse
    {
        $issueRecord = Issues::where('issues_id', $id)->first();

        if (!$issueRecord) {
            return response()->json(['error' => 'Issue not found.'], 404);
        }

        $issue = $issueRecord->issues_json;

        $openProjectName = $issueRecord->project->openproject_name ?? null;
        $userOpenProjects = $issueRecord->project->userOpenProject ?? [];

        // Mengisi data user dalam array
        $dataUser = [];
        foreach ($userOpenProjects as $userOpenProject) {
            $openProjectUserId = $userOpenProject->memberships_id ?? null;
            $openProjectUserName = $userOpenProject->name ?? null;
            $openProjectUserHref = $userOpenProject->user_href ?? null;

            $dataUser[] = [
                'openProjectUserId' => $openProjectUserId,
                'openProjectUserName' => $openProjectUserName,
                'openProjectUserHref' => $openProjectUserHref,
            ];
        }

        return response()->json([
            'status' => 'success',
            'issue' => [
                'id' => $issue['id'],
                'shareId' => $issue['shareId'] ?? null,
                'shortId' => $issue['shortId'],
                'title' => $issue['title'],
                'culprit' => $issue['culprit'],
                'permalink' => $issue['permalink'],
                'logger' => $issue['logger'] ?? null,
                'level' => $issue['level'],
                'status' => $issue['status'],
                'platform' => $issue['platform'],
                'project' => $issue['project'],
                'type' => $issue['type'],
                'metadata' => $issue['metadata'] ?? [],
                'count' => $issue['count'],
                'userCount' => $issue['userCount'],
                'firstSeen' => $issue['firstSeen'],
                'lastSeen' => $issue['lastSeen'],
                'firstRelease' => $issue['firstRelease'] ?? null,
                'lastRelease' => $issue['lastRelease'] ?? null,
                'activity' => $issue['activity'] ?? [],
                'stats' => $issue['stats'] ?? [],
                'assignees' => [
                    'assignee_id' => $issueRecord->openProject->assignee_id ?? null,
                    'assignee_name' => $issueRecord->openProject->assignee_name ?? 'unassigned',
                ],
                'openProject' => $openProjectName,
                'data_user' => $dataUser,


            ],
        ], 200);
    }
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['resolved', 'unresolved', 'ignored'])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $url = "{$this->baseUrl}issues/{$id}/";

        $data = ['status' => $newStatus];

        try {
            $response = $this->client->request('PUT', $url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            if ($response->getStatusCode() === 200) {
                return response()->json(['success' => 'Issue status updated successfully.'], 200);
            } else {
                return response()->json(['error' => 'Failed to update issue status.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update issue status: ' . $e->getMessage()], 500);
        }
    }
    public function showEvents($issueId): JsonResponse
    {
        $events = Events::where('issues_id', $issueId)
            ->get();

        // Filter data as needed
        $filteredEvents = $events->map(function ($event) {
            $eventData = $event->events_json;

            // Kembalikan data yang dibutuhkan dari events_json
            return [
                'id' => $eventData['id'],
                'event_type' => $eventData['event.type'],  // Sesuaikan dengan nama kolom di dalam events_json
                'title' => $eventData['title'],
                'message' => $eventData['message'],
                'location' => $eventData['location'],
                'tags' => $eventData['tags'],
            ];
        });

        // Pagination logic (optional if you need pagination)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $filteredEvents->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedEvents = new LengthAwarePaginator(
            $currentPageItems,
            $filteredEvents->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json($paginatedEvents);
    }
    public function showStackTrace($issueId): JsonResponse
    {

        $stackTrace = StackTrace::where('issues_id', $issueId)
            ->get();

        $filteredStackTrace = $stackTrace->map(function ($stackTraces) {
            $stackTraceData = $stackTraces->stack_trace_json;
            return $stackTraceData;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $filteredStackTrace->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedStackTrace = new LengthAwarePaginator(
            $currentPageItems,
            $filteredStackTrace->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json($paginatedStackTrace);
    }

}
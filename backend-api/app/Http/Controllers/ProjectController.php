<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Issues;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{

    public function viewProjects(): JsonResponse
    {
        $projectQuery = Project::withCount('issues')
                            ->addSelect('project_slug','project_json')
                            ->get();

        $filteredProjects = $projectQuery->map(function ($project) {
            $projectData = $project->project_json;

            return [
                'slug' => $project->project_slug,
                'name' => $projectData['name'],
                'status' => $projectData['status'] ?? null,
                'platform' => $projectData['platform'] ?? null,
                'totalIssue' => $project->issues_count ?? 0,
                'openproject_id' => $project->openproject_id ?? null,
                'openproject_name' => $project->openproject_name ?? null,
            ];
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $filteredProjects->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedProjects = new LengthAwarePaginator(
            $currentPageItems,
            $filteredProjects->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json($paginatedProjects);
    }
    public function showIssuesByProject(Request $request, $slug): JsonResponse
    {
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'desc');
        $perPage = 15;
    
        $query = Issues::where('project_slug', $slug);
    
        if ($status !== 'all') {
            $query->whereJsonContains('issues_json->status', $status);
        }
    
        $query->orderBy('issues_json->lastSeen', $sort);
    
        $projectIssues = $query->paginate($perPage);
    
        $currentPageItems = $projectIssues->getCollection()->map(function ($issue) {
            $issueData = $issue->issues_json;
    
            return [
                'id' => $issueData['id'],
                'shortId' => $issueData['shortId'],
                'slug' => $issueData['project']['slug'],
                'platform' => $issueData['project']['platform'],
                'title' => $issueData['title'],
                'permalink' => $issueData['permalink'],
                'logger' => $issueData['logger'],
                'level' => $issueData['level'],
                'status' => $issueData['status'],
                'type' => $issueData['type'],
                'count' => $issueData['count'],
                'userCount' => $issueData['userCount'] ?? null,
                'firstSeen' => $issueData['firstSeen'],
                'lastSeen' => $issueData['lastSeen'],
                'chart_stats' => $issueData['stats'],
            ];
        });
    
        $paginatedProjectIssue = $projectIssues->setCollection($currentPageItems);
    
        return response()->json($paginatedProjectIssue);
    }
      
}
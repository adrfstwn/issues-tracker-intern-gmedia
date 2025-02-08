<?php

namespace App\Http\Controllers;

use App\Models\Issues;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function countIssues($type, $slug = null): JsonResponse
    {
        try {
            $query = Issues::query();

            // Filter by slug if provided
            if ($slug) {
                $query->where('project_slug', $slug);
            }

            // Count based on type
            switch ($type) {
                case 'total':
                    $count = $query->count();
                    break;
                case 'unresolved':
                    $count = $query->where('issues_json->status', 'unresolved')->count();
                    break;
                case 'resolved':
                    $count = $query->where('issues_json->status', 'resolved')->count();
                    break;
                case 'ignored':
                    $count = $query->where('issues_json->status', 'ignored')->count();
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            return response()->json([
                'slug' => $slug ?? 'all',
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching issues: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}

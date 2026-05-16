<?php


namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectHistoryResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectHistoryController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(Request $request, string $projectId): AnonymousResourceCollection
    {
        $project = $this->service->findById($projectId);

        $this->authorize('view', $project);

        $history = $this->service->getHistory($project, (int) $request->query('per_page', 25));

        return ProjectHistoryResource::collection($history);
    }
}

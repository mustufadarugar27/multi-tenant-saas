<?php


namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\ListProjectsRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(ListProjectsRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->service->paginate($request->filters(), $request->perPage());

        return ProjectResource::collection($projects);
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = $this->service->create($request->toDTO(), $request->user());

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $id): ProjectResource
    {
        $project = $this->service->findById($id);

        $this->authorize('view', $project);

        return new ProjectResource($project->loadMissing('creator'));
    }

    public function update(UpdateProjectRequest $request, string $id): ProjectResource
    {
        $project = $this->service->findById($id);

        $this->authorize('update', $project);

        $updated = $this->service->update($project, $request->toDTO(), $request->user());

        return new ProjectResource($updated->loadMissing('creator'));
    }

    public function destroy(Request $request, string $id): Response
    {
        $project = $this->service->findById($id);

        $this->authorize('delete', $project);

        $this->service->delete($project, $request->user());

        return response()->noContent();
    }

    public function restore(Request $request, string $id): ProjectResource
    {
        $project = Project::withTrashed()->where('id', $id)->firstOrFail();

        $this->authorize('restore', $project);

        $restored = $this->service->restore($project, $request->user());

        return new ProjectResource($restored);
    }
}

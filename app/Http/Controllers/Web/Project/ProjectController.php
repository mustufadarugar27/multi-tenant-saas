<?php


namespace App\Http\Controllers\Web\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\ListProjectsRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Support\EnumConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(ListProjectsRequest $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->service->paginate($request->filters(), $request->perPage());

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->filters(),
            'statuses' => EnumConfig::options('project_status'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', [
            'statuses' => EnumConfig::options('project_status'),
        ]);
    }

    public function store(CreateProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $project = $this->service->create($request->toDTO(), $request->user());

        return redirect()
            ->route('tenant.projects.show', $project)
            ->with('success', "Project created successfully.");
    }

    public function show(Request $request, string $id): View
    {
        $project = $this->service->findById($id);

        $this->authorize('view', $project);
        return view('projects.show', [
            'project' => $project->loadMissing('creator'),
            'projectTasks' => $project->tasks()->paginate(10),
            'history' => $this->service->getHistory($project, 10),
        ]);
    }

    public function edit(string $id): View
    {
        $project = $this->service->findById($id);

        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
            'statuses' => EnumConfig::options('project_status'),
        ]);
    }

    public function update(UpdateProjectRequest $request, string $id): RedirectResponse
    {
        $project = $this->service->findById($id);

        $this->authorize('update', $project);

        $updated = $this->service->update($project, $request->toDTO(), $request->user());

        return redirect()
            ->route('tenant.projects.show', $updated)
            ->with('success', "Project updated successfully.");
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $project = $this->service->findById($id);

        $this->authorize('delete', $project);

        $this->service->delete($project, $request->user());

        return redirect()
            ->route('tenant.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function restore(Request $request, string $id): RedirectResponse
    {
        $project = Project::withTrashed()->where('id', $id)->firstOrFail();

        $this->authorize('restore', $project);

        $restored = $this->service->restore($project, $request->user());

        return redirect()
            ->route('tenant.projects.show', $restored)
            ->with('success', "Project restored successfully.");
    }
}

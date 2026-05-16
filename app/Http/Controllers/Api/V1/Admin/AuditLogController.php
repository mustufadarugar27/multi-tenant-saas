<?php


namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAuditLogsRequest;
use App\Http\Resources\Admin\AuditLogResource;
use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $repository,
    ) {}

    public function index(ListAuditLogsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = $this->repository->paginate(
            $request->filters(),
            $request->integer('per_page', 25),
        );

        return response()->json([
            'data' => AuditLogResource::collection($logs->items()),
            'meta' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    public function show(string $id): AuditLogResource
    {
        $log = AuditLog::findOrFail($id);

        $this->authorize('view', $log);

        return new AuditLogResource($log->load('actor:id,name,email'));
    }
}

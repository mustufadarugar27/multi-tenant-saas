<?php


namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);

        Model::shouldBeStrict(! app()->isProduction());

        if (! app()->isProduction()) {
            DB::listen(function (object $query): void {
                if ($query->time > 500) {
                    logger()->warning('Slow query detected.', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }
    }
}

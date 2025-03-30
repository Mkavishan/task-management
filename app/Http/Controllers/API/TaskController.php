<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Asset;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(): TaskCollection
    {
        return new TaskCollection(Task::paginate(10));
    }

    public function show(Task $task): TaskResource
    {
        $task->load('assets');
        return new TaskResource($task);
    }

    public function store(TaskRequest $taskRequest): TaskResource | JsonResponse
    {
        try {
            return DB::transaction(function () use ($taskRequest) {
                $task = Task::create($taskRequest->validated());

                // Set new file.
                $this->uploadNewAssets($taskRequest, $task);

                return new TaskResource($task);
            });
        } catch (\Throwable $e) {
            Log::error('Task creation failed: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to create task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(TaskRequest $taskRequest, Task $task): TaskResource | JsonResponse
    {
        // TODO: Check the user has permission to update the Task. (Task should be updated by the Task owner)

        try {
            return DB::transaction(function () use ($taskRequest, $task) {
                $task->update($taskRequest->validated());

                // Set new files.
                $this->uploadNewAssets($taskRequest, $task);

                // Todo: Handle scenario if files were deleted.

                return new TaskResource($task);
            });
        } catch (\Throwable $e) {
            Log::error('Task could not be updated ' . $e->getMessage());

            return response()->json(['error' => 'Failed to update task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            return DB::transaction(function () use ($task) {
                if ($task->assets()->exists()) {
                    foreach ($task->assets as $asset) {
                        Storage::delete($asset->path);
                    }

                    $task->assets()->delete();
                }

                $task->delete();

                return response()->json(null, Response::HTTP_NO_CONTENT);
            });
        } catch (\Throwable $e) {
            Log::error('Task deletion failed: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to delete task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store new files for the given Task.
     *
     * @param TaskRequest $taskRequest
     * @param $task
     * @return void
     */
    private function uploadNewAssets(TaskRequest $taskRequest, $task): void
    {
        $taskRequest->when($taskRequest->hasFile('assets'), function () use ($taskRequest, $task) {
            $assets = collect($taskRequest->file('assets'))
                ->filter
                ->isValid()
                ->map(fn($file) => [
                    'task_id' => $task->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store("tasks/$task->id"),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            if ($assets->isNotEmpty()) {
                Asset::insert($assets->toArray());
            }
        });
    }
}

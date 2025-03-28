<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
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
        return new TaskResource($task);
    }

    public function store(TaskRequest $taskRequest): TaskResource
    {
        $task = Task::create($taskRequest->toArray());

        return new TaskResource($task);
    }

    public function update(TaskRequest $taskRequest, Task $task): TaskResource
    {
        $task->update($taskRequest->toArray());

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            DB::transaction(function () use ($task) {
                if ($task->assets()->exists()) {
                    foreach ($task->assets as $asset) {
                        Storage::delete($asset->url);
                    }

                    $task->assets()->delete();
                }

                $task->delete();
            });

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            Log::error('Task deletion failed: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to delete task'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

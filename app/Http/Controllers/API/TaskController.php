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
    public function index(): JsonResponse
    {
        // TODO: Handle pagination.
        return response()->json(new TaskCollection(Task::all()), Response::HTTP_OK);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json(new TaskResource($task), Response::HTTP_OK);
    }

    public function store(TaskRequest $taskRequest): JsonResponse
    {
        $task = Task::create($taskRequest->toArray());

        return response()->json($task, Response::HTTP_CREATED);
    }

    public function update(TaskRequest $taskRequest, Task $task): JsonResponse
    {
        $task->update($taskRequest->toArray());

        return response()->json(null, Response::HTTP_NO_CONTENT);
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

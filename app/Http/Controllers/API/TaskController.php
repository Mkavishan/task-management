<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(new TaskCollection(Task::all()), Response::HTTP_OK);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json(new TaskResource($task), Response::HTTP_OK);
    }

    public function store(TaskRequest $taskRequest): JsonResponse
    {
        $task = Task::create($taskRequest);

        return response()->json($task, Response::HTTP_CREATED);
    }

    public function update(TaskRequest $taskRequest, Task $task): JsonResponse
    {
        $task::update($taskRequest);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->assets) {
            foreach ($task->assets as $asset) {
                Storage::delete($asset);
            }

            $task->assets()->delete();
        }

        $task->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

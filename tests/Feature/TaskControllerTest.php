<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    private User $user;
    private string $guard = 'sanctum';

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();;

    }

    /**
     * A basic feature test example.
     */
    public function test_create_task(): void
    {
        $uri = route('tasks.store');
        $requestData = [
            'name' => 'Test Task'
        ];

        // Can't create a new Task without an authenticated user.
        $response = $this->postJson($uri, $requestData);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Name field is a mandatory field.
        $response = $this->actingAs($this->user, $this->guard)->postJson($uri, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message', 'errors' => ['name']]);

        // Create a Task with proper data.
        $response = $this->actingAs($this->user, $this->guard)->postJson($uri, $requestData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'name', 'created_at', 'updated_at']]);
    }

    public function test_show_task(): void
    {
        $response = $this->actingAs($this->user, $this->guard)
            ->postJson(route('tasks.store'), ['name' => 'Test Task']);
        $response->assertStatus(Response::HTTP_CREATED);
        $taskId = $response->json('data.id');

        $taskViewUri = "api/tasks/$taskId";

        $this->app['auth']->forgetGuards(); // Reset auth guards

        // User should not see the task if unauthenticated.
        $task = $this->getJson(route('tasks.show', $taskId));
        $task->assertStatus(Response::HTTP_UNAUTHORIZED);

        // Authenticated user should see the task.
        $task = $this->actingAs($this->user, $this->guard)->getJson($taskViewUri);
        $task->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => ['id', 'name', 'created_at', 'updated_at']]);

        // Test Task Not Found.
        $this->actingAs($this->user, $this->guard)
            ->getJson(route('tasks.show', ['task' => 999999])) // Non-existent ID
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

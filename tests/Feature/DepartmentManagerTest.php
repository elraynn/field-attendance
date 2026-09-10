<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_departments(): void
    {
        Department::factory()->create(['name' => 'Human Resources']);

        Livewire::test('department-manager')
            ->assertSee('Human Resources');
    }

    public function test_it_creates_a_department(): void
    {
        Livewire::test('department-manager')
            ->call('create')
            ->set('name', 'Finance')
            ->set('code', 'FIN')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('departments', ['name' => 'Finance', 'code' => 'FIN']);
    }

    public function test_it_requires_a_unique_name(): void
    {
        Department::factory()->create(['name' => 'Finance']);

        Livewire::test('department-manager')
            ->call('create')
            ->set('name', 'Finance')
            ->call('save')
            ->assertHasErrors('name');
    }

    public function test_it_updates_a_department(): void
    {
        $department = Department::factory()->create(['name' => 'Old Name']);

        Livewire::test('department-manager')
            ->call('edit', $department->id)
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'New Name']);
    }

    public function test_it_deletes_a_department(): void
    {
        $department = Department::factory()->create();

        Livewire::test('department-manager')->call('delete', $department->id);

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_search_filters_the_list(): void
    {
        Department::factory()->create(['name' => 'Engineering']);
        Department::factory()->create(['name' => 'Marketing']);

        Livewire::test('department-manager')
            ->set('search', 'Engineering')
            ->assertSee('Engineering')
            ->assertDontSee('Marketing');
    }
}

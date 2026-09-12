<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_employees(): void
    {
        Employee::factory()->create(['first_name' => 'Johan']);

        Livewire::test('employee-manager')
            ->assertSee('Johan');
    }

    public function test_it_creates_an_employee(): void
    {
        $department = Department::factory()->create();

        Livewire::test('employee-manager')
            ->call('create')
            ->set('code', 'EMP001')
            ->set('first_name', 'Budi')
            ->set('last_name', 'Santoso')
            ->set('email', 'budi@example.com')
            ->set('birth_date', '1995-01-01')
            ->set('gender', 'male')
            ->set('join_date', '2024-01-01')
            ->set('department_id', $department->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'email' => 'budi@example.com',
            'department_id' => $department->id,
        ]);
    }

    public function test_it_requires_a_code(): void
    {
        $department = Department::factory()->create();

        Livewire::test('employee-manager')
            ->call('create')
            ->set('code', '')
            ->set('first_name', 'Budi')
            ->set('last_name', 'Santoso')
            ->set('email', 'budi2@example.com')
            ->set('birth_date', '1995-01-01')
            ->set('join_date', '2024-01-01')
            ->set('department_id', $department->id)
            ->call('save')
            ->assertHasErrors('code');
    }

    public function test_it_requires_a_first_name(): void
    {
        Livewire::test('employee-manager')
            ->call('create')
            ->set('first_name', '')
            ->call('save')
            ->assertHasErrors('first_name');
    }

    public function test_it_requires_a_last_name(): void
    {
        Livewire::test('employee-manager')
            ->call('create')
            ->set('last_name', '')
            ->call('save')
            ->assertHasErrors('last_name');
    }

    public function test_it_requires_a_valid_email_format(): void
    {
        Livewire::test('employee-manager')
            ->call('create')
            ->set('email', 'bukan-email')
            ->call('save')
            ->assertHasErrors('email');
    }

    public function test_it_requires_a_unique_email(): void
    {
        Employee::factory()->create(['email' => 'dipakai@example.com']);

        Livewire::test('employee-manager')
            ->call('create')
            ->set('email', 'dipakai@example.com')
            ->call('save')
            ->assertHasErrors('email');
    }

    public function test_it_only_accepts_valid_gender_values(): void
    {
        Livewire::test('employee-manager')
            ->call('create')
            ->set('gender', 'alien')
            ->call('save')
            ->assertHasErrors('gender');
    }

    public function test_it_requires_an_existing_department(): void
    {
        Livewire::test('employee-manager')
            ->call('create')
            ->set('department_id', 99999)
            ->call('save')
            ->assertHasErrors('department_id');
    }

    public function test_it_updates_an_employee(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Old Name']);

        Livewire::test('employee-manager')
            ->call('edit', $employee->id)
            ->set('first_name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'first_name' => 'New Name']);
    }

    public function test_it_deletes_an_employee(): void
    {
        $employee = Employee::factory()->create();

        Livewire::test('employee-manager')->call('delete', $employee->id);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_search_filters_the_list(): void
    {
        Employee::factory()->create(['first_name' => 'Engineering Joe']);
        Employee::factory()->create(['first_name' => 'Marketing Jane']);

        Livewire::test('employee-manager')
            ->set('search', 'Engineering')
            ->assertSee('Engineering Joe')
            ->assertDontSee('Marketing Jane');
    }
}

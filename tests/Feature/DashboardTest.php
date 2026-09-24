<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_it_shows_the_right_counts(): void
    {
        Department::factory()->count(2)->create();
        $department = Department::factory()->create();

        $employees = Employee::factory()->count(3)->create(['department_id' => $department->id, 'is_active' => true]);
        Employee::factory()->create(['department_id' => $department->id, 'is_active' => false]);

        LeaveRequest::factory()->create(['employee_id' => $employees->first()->id, 'status' => 'diajukan']);
        LeaveRequest::factory()->create(['employee_id' => $employees->first()->id, 'status' => 'disetujui']);

        Livewire::test('dashboard')
            ->assertViewHas('totalDepartments', 3)
            ->assertViewHas('totalEmployees', 3)
            ->assertViewHas('pendingLeaveRequests', 1);
    }
}

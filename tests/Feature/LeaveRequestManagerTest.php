<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveRequestManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Default to a logged-in employee; tests that need HR-specific
        // behaviour switch users with their own actingAs() call.
        $this->actingAs(User::factory()->create(['role' => 'employee']));
    }

    public function test_it_lists_leave_requests(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Budi']);
        LeaveRequest::factory()->create(['employee_id' => $employee->id]);

        Livewire::test('leave-request-manager')
            ->assertSee('Budi');
    }

    public function test_it_submits_a_leave_request_with_status_diajukan(): void
    {
        $employee = Employee::factory()->create();

        Livewire::test('leave-request-manager')
            ->call('create')
            ->set('employee_id', $employee->id)
            ->set('type', 'cuti')
            ->set('start_date', '2026-12-01')
            ->set('end_date', '2026-12-03')
            ->set('reason', 'Liburan keluarga')
            ->call('submitRequest')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'status' => 'diajukan',
        ]);
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        $employee = Employee::factory()->create();

        Livewire::test('leave-request-manager')
            ->call('create')
            ->set('employee_id', $employee->id)
            ->set('start_date', '2026-12-05')
            ->set('end_date', '2026-12-01')
            ->set('reason', 'Test')
            ->call('submitRequest')
            ->assertHasErrors('end_date');
    }

    public function test_hr_can_approve_a_pending_request(): void
    {
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'diajukan']);

        $this->actingAs(User::factory()->create(['role' => 'hr']));

        Livewire::test('leave-request-manager')
            ->call('decide', $leaveRequest->id, 'disetujui')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leave_requests', ['id' => $leaveRequest->id, 'status' => 'disetujui']);
    }

    public function test_hr_can_reject_a_pending_request(): void
    {
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'diajukan']);

        $this->actingAs(User::factory()->create(['role' => 'hr']));

        Livewire::test('leave-request-manager')
            ->call('decide', $leaveRequest->id, 'ditolak')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leave_requests', ['id' => $leaveRequest->id, 'status' => 'ditolak']);
    }

    public function test_an_employee_role_cannot_approve_a_request(): void
    {
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'diajukan']);

        // Default logged-in user from setUp() already has the 'employee' role.
        Livewire::test('leave-request-manager')
            ->call('decide', $leaveRequest->id, 'disetujui')
            ->assertHasErrors('workflow');

        $this->assertDatabaseHas('leave_requests', ['id' => $leaveRequest->id, 'status' => 'diajukan']);
    }

    public function test_an_already_decided_request_cannot_be_transitioned_again(): void
    {
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'disetujui']);

        $this->actingAs(User::factory()->create(['role' => 'hr']));

        Livewire::test('leave-request-manager')
            ->call('decide', $leaveRequest->id, 'ditolak')
            ->assertHasErrors('workflow');

        $this->assertDatabaseHas('leave_requests', ['id' => $leaveRequest->id, 'status' => 'disetujui']);
    }
}

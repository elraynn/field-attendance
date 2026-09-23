<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleBulkAssignTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_a_shift_to_multiple_employees_at_once(): void
    {
        $shift = Shift::factory()->create();
        $employees = Employee::factory()->count(3)->create();

        Livewire::test('schedule-bulk-assign')
            ->set('date', '2026-11-01')
            ->set('shift_id', $shift->id)
            ->set('employee_ids', $employees->pluck('id')->all())
            ->call('save')
            ->assertHasNoErrors();

        foreach ($employees as $employee) {
            $this->assertDatabaseHas('schedules', [
                'employee_id' => $employee->id,
                'shift_id' => $shift->id,
                'date' => '2026-11-01',
            ]);
        }
    }

    public function test_it_skips_employees_already_scheduled_that_day_instead_of_failing(): void
    {
        $shift = Shift::factory()->create();
        $alreadyScheduled = Employee::factory()->create();
        $free = Employee::factory()->create();

        Schedule::factory()->create([
            'employee_id' => $alreadyScheduled->id,
            'shift_id' => $shift->id,
            'date' => '2026-11-01',
        ]);

        Livewire::test('schedule-bulk-assign')
            ->set('date', '2026-11-01')
            ->set('shift_id', $shift->id)
            ->set('employee_ids', [$alreadyScheduled->id, $free->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schedules', [
            'employee_id' => $free->id,
            'date' => '2026-11-01',
        ]);

        // The already-scheduled employee should still only have their original row.
        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_it_requires_at_least_one_employee(): void
    {
        $shift = Shift::factory()->create();

        Livewire::test('schedule-bulk-assign')
            ->set('date', '2026-11-01')
            ->set('shift_id', $shift->id)
            ->set('employee_ids', [])
            ->call('save')
            ->assertHasErrors('employee_ids');
    }
}

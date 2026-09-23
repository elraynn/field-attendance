<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_employees_and_the_current_week(): void
    {
        Employee::factory()->create(['first_name' => 'Budi']);

        Livewire::test('schedule-calendar')
            ->assertSee('Budi');
    }

    public function test_selecting_an_empty_cell_and_assigning_creates_a_schedule(): void
    {
        $employee = Employee::factory()->create();
        $shift = Shift::factory()->create();
        $date = now()->startOfWeek()->format('Y-m-d');

        Livewire::test('schedule-calendar')
            ->call('selectCell', $employee->id, $date)
            ->set('shift_id', $shift->id)
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schedules', [
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'date' => $date,
        ]);
    }

    public function test_selecting_a_filled_cell_and_assigning_updates_it_instead_of_duplicating(): void
    {
        $employee = Employee::factory()->create();
        $oldShift = Shift::factory()->create();
        $newShift = Shift::factory()->create();
        $date = now()->startOfWeek()->format('Y-m-d');

        $schedule = Schedule::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => $oldShift->id,
            'date' => $date,
        ]);

        Livewire::test('schedule-calendar')
            ->call('selectCell', $employee->id, $date, $schedule->id, $oldShift->id)
            ->set('shift_id', $newShift->id)
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('schedules', 1);
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id, 'shift_id' => $newShift->id]);
    }

    public function test_removing_an_assignment_deletes_it(): void
    {
        $employee = Employee::factory()->create();
        $shift = Shift::factory()->create();
        $date = now()->startOfWeek()->format('Y-m-d');

        $schedule = Schedule::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'date' => $date,
        ]);

        Livewire::test('schedule-calendar')
            ->call('selectCell', $employee->id, $date, $schedule->id, $shift->id)
            ->call('removeAssignment');

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_navigating_weeks_changes_the_displayed_range(): void
    {
        $thisWeek = now()->startOfWeek()->format('Y-m-d');
        $nextWeek = Carbon::parse($thisWeek)->addWeek()->format('Y-m-d');

        Livewire::test('schedule-calendar')
            ->assertSet('weekStart', $thisWeek)
            ->call('nextWeek')
            ->assertSet('weekStart', $nextWeek)
            ->call('previousWeek')
            ->assertSet('weekStart', $thisWeek);
    }
}

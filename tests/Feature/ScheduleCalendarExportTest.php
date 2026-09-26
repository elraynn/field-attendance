<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ScheduleCalendarExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_excel_export_downloads_a_spreadsheet(): void
    {
        $response = $this->get(route('schedules.calendar.export.excel', ['week' => '2026-09-21']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_pdf_export_downloads_a_pdf(): void
    {
        $response = $this->get(route('schedules.calendar.export.pdf', ['week' => '2026-09-21']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_places_the_shift_under_the_right_day_column(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Budi', 'last_name' => 'Santoso']);
        $morningShift = Shift::factory()->create(['name' => 'Shift Pagi']);

        // Selasa, 2026-09-22 — second day of the week starting 2026-09-21 (Senin).
        Schedule::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => $morningShift->id,
            'date' => '2026-09-22',
        ]);

        $response = $this->get(route('schedules.calendar.export.excel', ['week' => '2026-09-21']));

        $tempFile = tempnam(sys_get_temp_dir(), 'calendar-export-test').'.xlsx';
        file_put_contents($tempFile, $response->getContent());
        $sheet = IOFactory::load($tempFile)->getActiveSheet();
        unlink($tempFile);

        // Row 1 is the title, row 2 is blank, row 3 is the header, row 4+ is data
        // — that's the title-pushes-header-down behaviour table-export documents.
        $this->assertSame('Sel 22/09', $sheet->getCell('C3')->getValue());
        $this->assertSame('Budi Santoso', $sheet->getCell('A4')->getValue());
        $this->assertSame('Shift Pagi', $sheet->getCell('C4')->getValue());
        $this->assertSame('-', $sheet->getCell('B4')->getValue());
    }

    public function test_export_defaults_to_the_current_week_when_none_given(): void
    {
        $this->get(route('schedules.calendar.export.excel'))->assertOk();
    }
}

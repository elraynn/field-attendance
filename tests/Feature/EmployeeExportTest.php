<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_excel_export_downloads_a_spreadsheet(): void
    {
        Employee::factory()->count(3)->create();

        $response = $this->get(route('employees.export.excel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment;filename="daftar-karyawan.xlsx"');
    }

    public function test_pdf_export_downloads_a_pdf(): void
    {
        Employee::factory()->count(3)->create();

        $response = $this->get(route('employees.export.pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_works_with_no_employees(): void
    {
        $this->get(route('employees.export.excel'))->assertOk();
        $this->get(route('employees.export.pdf'))->assertOk();
    }
}

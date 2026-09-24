<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Dompdf\Dompdf;
use Elrayn\TableExport\Export;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeExportController extends Controller
{
    public function excel(): Response
    {
        $spreadsheet = $this->export()->toSpreadsheet();

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="daftar-karyawan.xlsx"',
        ]);
    }

    public function pdf(): Response
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->export()->toHtml());
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment;filename="daftar-karyawan.pdf"',
        ]);
    }

    private function export(): Export
    {
        return Export::make($this->rows())
            ->title('Daftar Karyawan')
            ->columns([
                'code' => 'Kode',
                'name' => 'Nama',
                'email' => 'Email',
                'department' => 'Departemen',
                'status' => 'Status',
            ]);
    }

    private function rows(): array
    {
        return Employee::with('department')
            ->orderBy('first_name')
            ->get()
            ->map(fn (Employee $employee) => [
                'code' => $employee->code,
                'name' => trim("{$employee->first_name} {$employee->last_name}"),
                'email' => $employee->email,
                'department' => $employee->department?->name ?? '-',
                'status' => $employee->is_active ? 'Aktif' : 'Nonaktif',
            ])
            ->all();
    }
}

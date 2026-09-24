<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Dompdf\Dompdf;
use Elrayn\TableExport\Export;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LeaveRequestExportController extends Controller
{
    //
    public function excel(): Response
    {
        $spreadsheet = $this->export()->toSpreadsheet();

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="daftar-permintaan-izin.xlsx"',
        ]);
    }

    public function pdf(): Response
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->export()->toHtml());
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment;filename="daftar-permintaan-izin.pdf"',
        ]);
    }

    private function export(): Export
    {
        return Export::make($this->rows())
            ->title('Daftar Permintaan Izin')
            ->columns([
                'employee' => 'Karyawan',
                'type' => 'Jenis Izin',
                'start_date' => 'Dari Tanggal',
                'end_date' => 'Sampai Tanggal',
                'reason' => 'Alasan',
                'status' => 'Status',

            ]);
    }

    private function rows(): array
    {
        return LeaveRequest::with('employee')
            ->orderBy('start_date')
            ->get()
            ->map(fn (LeaveRequest $leave) => [
                'type' => $leave->type,
                'employee' => trim("{$leave->employee->first_name} {$leave->employee->last_name}"),
                'reason' => $leave->reason,
                'end_date' => $leave->end_date,
                'start_date' => $leave->start_date,
                'status' => $leave->status,
            ])
            ->all();
    }
}

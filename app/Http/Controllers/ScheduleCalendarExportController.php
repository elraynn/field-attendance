<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Elrayn\TableExport\Export;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleCalendarExportController extends Controller
{
    public function excel(Request $request): Response
    {
        $spreadsheet = $this->export($request)->toSpreadsheet();

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="jadwal-shift-mingguan.xlsx"',
        ]);
    }

    public function pdf(Request $request): Response
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->export($request)->toHtml());
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment;filename="jadwal-shift-mingguan.pdf"',
        ]);
    }

    private function export(Request $request): Export
    {
        $weekStart = Carbon::parse($request->query('week', now()->startOfWeek()->format('Y-m-d')));

        return Export::make($this->rows($weekStart))
            ->title('Jadwal Shift Minggu '.$weekStart->format('d/m/Y'))
            ->columns($this->columns($weekStart));
    }

    private function columns(Carbon $weekStart): array
    {
        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $columns = ['employee' => 'Karyawan'];

        foreach (range(0, 6) as $i) {
            $date = $weekStart->copy()->addDays($i);
            $columns[$date->format('Y-m-d')] = $dayNames[$date->dayOfWeek].' '.$date->format('d/m');
        }

        return $columns;
    }

    private function rows(Carbon $weekStart): array
    {
        $dates = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $schedules = Schedule::whereBetween('date', [$dates->first()->format('Y-m-d'), $dates->last()->format('Y-m-d')])
            ->with('shift')
            ->get();

        $scheduleMap = [];
        foreach ($schedules as $schedule) {
            $scheduleMap[$schedule->employee_id][$schedule->date] = $schedule;
        }

        return Employee::where('is_active', true)
            ->orderBy('first_name')
            ->get()
            ->map(function (Employee $employee) use ($dates, $scheduleMap) {
                $row = ['employee' => trim("{$employee->first_name} {$employee->last_name}")];

                foreach ($dates as $date) {
                    $key = $date->format('Y-m-d');
                    $row[$key] = $scheduleMap[$employee->id][$key]->shift->name ?? '-';
                }

                return $row;
            })
            ->all();
    }
}

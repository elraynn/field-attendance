<?php

namespace App\Models;

use Database\Factories\LeaveRequestFactory;
use Elrayn\ApprovalFlow\Workflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    protected $fillable = ['employee_id', 'type', 'start_date', 'end_date', 'reason', 'status'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public static function workflow(): Workflow
    {
        return new Workflow([
            'diajukan' => ['disetujui' => 'hr', 'ditolak' => 'hr'],
        ]);
    }
}

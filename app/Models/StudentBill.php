<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentBill extends Model
{
    protected $fillable = [
        'student_id',
        'payment_type_id',
        'academic_year_id',
        'month',
        'month_order',
        'year',
        'amount',
        'amount_paid',
        'status',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'month_order' => 'integer',
        'year' => 'integer',
        'due_date' => 'date:Y-m-d',
    ];

    protected $appends = ['remaining_amount'];

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}

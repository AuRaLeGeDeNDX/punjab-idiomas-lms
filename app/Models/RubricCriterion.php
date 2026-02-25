<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'rubric_id',
        'criterion_name',
        'criterion_description',
        'max_points',
        'order_index',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
        'order_index' => 'integer',
    ];

    /**
     * Get the rubric this criterion belongs to.
     */
    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    /**
     * Calculate percentage for a given score.
     */
    public function getPercentage(float $score): float
    {
        if ($this->max_points == 0) {
            return 0;
        }

        return ($score / $this->max_points) * 100;
    }
}

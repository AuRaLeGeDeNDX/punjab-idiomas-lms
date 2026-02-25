<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'title',
        'description',
    ];

    /**
     * Get the assignment this rubric belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the criteria for this rubric.
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('order_index');
    }

    /**
     * Calculate total possible points for this rubric.
     */
    public function getTotalPoints(): float
    {
        return $this->criteria()->sum('max_points');
    }

    /**
     * Calculate score from rubric criterion scores.
     */
    public function calculateScore(array $criterionScores): float
    {
        $totalScore = 0;
        
        foreach ($this->criteria as $criterion) {
            if (isset($criterionScores[$criterion->id])) {
                $score = min($criterionScores[$criterion->id], $criterion->max_points);
                $totalScore += $score;
            }
        }

        return $totalScore;
    }

    /**
     * Validate criterion scores against rubric.
     */
    public function validateScores(array $criterionScores): bool
    {
        foreach ($criterionScores as $criterionId => $score) {
            $criterion = $this->criteria()->find($criterionId);
            
            if (!$criterion) {
                return false;
            }
            
            if ($score < 0 || $score > $criterion->max_points) {
                return false;
            }
        }

        return true;
    }
}

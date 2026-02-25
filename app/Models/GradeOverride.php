<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeOverride extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grade_id',
        'admin_id',
        'original_score',
        'new_score',
        'reason',
        'overridden_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_score' => 'decimal:2',
        'new_score' => 'decimal:2',
        'overridden_at' => 'datetime',
    ];

    /**
     * Get the grade that was overridden.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the admin who performed the override.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the score difference.
     */
    public function getScoreDifference(): float
    {
        return (float) ($this->new_score - $this->original_score);
    }

    /**
     * Check if the override increased the score.
     */
    public function isIncrease(): bool
    {
        return $this->new_score > $this->original_score;
    }

    /**
     * Check if the override decreased the score.
     */
    public function isDecrease(): bool
    {
        return $this->new_score < $this->original_score;
    }
}

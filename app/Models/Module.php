<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Module extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('module');
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'content',
        'order_index',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * Get the course that owns the module.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the assignments for the module.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get all subpages for this module.
     */
    public function subpages(): HasMany
    {
        return $this->hasMany(Subpage::class);
    }

    /**
     * Get active subpages for this module, ordered by order_index.
     */
    public function activeSubpages(): HasMany
    {
        return $this->subpages()->active()->ordered();
    }

    /**
     * Add content to the module.
     */
    public function addContent(array $content): void
    {
        $currentContent = $this->content ?? [];
        $this->content = array_merge($currentContent, $content);
        $this->save();
    }

    /**
     * Get the content attribute as an array.
     */
    public function getContentAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }
}

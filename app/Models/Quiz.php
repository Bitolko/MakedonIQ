<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'lesson_id',
        'title_en',
        'title_mk',
        'slug',
        'description_en',
        'description_mk',
        'difficulty',
        'estimated_minutes',
        'points_per_question',
        'is_published',
        'is_demo',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'lesson_id' => 'integer',
            'estimated_minutes' => 'integer',
            'points_per_question' => 'integer',
            'is_published' => 'boolean',
            'is_demo' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeDemo(Builder $query): Builder
    {
        return $query->where('is_demo', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title_en');
    }

    public function scopeBeginner(Builder $query): Builder
    {
        return $query->where('difficulty', 'beginner');
    }

    public function scopeIntermediate(Builder $query): Builder
    {
        return $query->where('difficulty', 'intermediate');
    }

    public function scopeAdvanced(Builder $query): Builder
    {
        return $query->where('difficulty', 'advanced');
    }
}

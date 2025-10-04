<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'period',
        'unit',
        'target_date',
        'teacher_id',
        'rev_academic_id',
        'rev_social_id'
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function revAcademic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rev_academic_id');
    }

    public function revSocial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rev_social_id');
    }

    public function projectDocuments(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'project_document_id',
        'reviewer_id',
        'stage',
        'decision',
        'observation'
    ];

    public function projectDocument(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

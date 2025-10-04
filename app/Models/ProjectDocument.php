<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectDocument extends Model
{
    protected $fillable = [
        'project_id',
        'document_type_id',
        'status',
        'last_observation',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function latestVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'id', 'project_document_id')
            ->orderBy('version', 'desc');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    protected $fillable = [
        'project_document_id',
        'original_name',
        'path',
        'mime',
        'size',
        'version'
    ];

    public function projectDocument(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class);
    }
}

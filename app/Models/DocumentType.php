<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'sequence',
        'allowed_mime',
        'max_mb'
    ];

    protected $casts = [
        'allowed_mime' => 'array',
    ];

    public function projectDocuments(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }
}

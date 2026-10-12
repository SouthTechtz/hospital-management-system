<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    protected $fillable = [
        'code', 'description', 'chapter', 'category', 'is_billable',
        'is_common_tanzania', 'keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
            'is_common_tanzania' => 'boolean',
        ];
    }

    public function scopeSearch($query, string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', $term.'%')
                ->orWhere('description', 'like', '%'.$term.'%');
        });
    }
}

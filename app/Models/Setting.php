<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get the typed value.
     */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            'integer' => (int) $this->value,
            default   => $this->value,
        };
    }

    /**
     * Scope to a specific group.
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}

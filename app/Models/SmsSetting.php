<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_name',
        'provider_class',
        'is_active',
        'is_default',
        'credentials',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'credentials' => 'array',
    ];

    /**
     * Get the default SMS provider
     */
    public static function getDefaultProvider()
    {
        return static::where('is_default', true)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get all active SMS providers
     */
    public static function getActiveProviders()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Set as default provider
     */
    public function setAsDefault()
    {
        // Remove default from all other providers
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this provider as default
        $this->update(['is_default' => true]);
    }
}

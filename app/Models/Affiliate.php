<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'email','subscription_type'];

    /**
     * Get the user that owns the affiliate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

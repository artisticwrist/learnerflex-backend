<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'bank_account',
        'bank_name',
        'email',
        'old_balance',
        'status',
    ];

    /**
     * Get the user associated with the withdrawal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

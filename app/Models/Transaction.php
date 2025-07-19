<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\MoneyCast;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'trx_id',
        'phone_number',
        'address',
        'total_amount',
        'product_id',
        'store_id',
        'duration',
        'is_paid',
        'delivery_type',
        'transaction_status',
        'transaction_url',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'total_amount' => MoneyCast::class
    ];

    public static function generateUniqueTrxId()
    {
        $prefix = 'SEWA';
        do {
            $randomString = $prefix . mt_rand(1000, 9999);
        } while (self::where('trx_id', $randomString)->exists());

        return $randomString;
    } 

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function testimonials()
    {
        return $this->hasOne(Testimonial::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

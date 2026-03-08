<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\MoneyCast;
use Illuminate\Support\Str;

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
        'ended_at',
        'status',
        'qty',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'total_amount' => MoneyCast::class,
        'is_paid'     => 'boolean',
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

    protected static function booted(): void
    {
        static::updated(function (Transaction $transaction) {

            // ✅ Trigger 1: is_paid berubah → kelola stok
            if ($transaction->wasChanged('is_paid')) {
                $wasPaid = (bool) $transaction->getOriginal('is_paid');
                $isPaid  = (bool) $transaction->is_paid;

                // false → true: baru dibayar → kurangi stok sejumlah qty
                if (!$wasPaid && $isPaid) {
                    self::decrementStock($transaction);
                }

                // true → false: pembayaran dibatalkan → kembalikan stok
                if ($wasPaid && !$isPaid) {
                    self::incrementStock($transaction);
                }
            }

            // ✅ Trigger 2: status selesai/dibatalkan → kembalikan stok
            if ($transaction->wasChanged('status')) {
                $oldStatus = $transaction->getOriginal('status');
                $newStatus = $transaction->status;

                if (
                    in_array($newStatus, ['selesai', 'dibatalkan']) &&
                    (bool) $transaction->is_paid === true
                ) {
                    self::incrementStock($transaction);
                }

                // Balik ke proses → kurangi stok lagi
                if (
                    $newStatus === 'proses' &&
                    in_array($oldStatus, ['selesai', 'dibatalkan']) &&
                    (bool) $transaction->is_paid === true
                ) {
                    self::decrementStock($transaction);
                }
            }
        });

        // Softdelete → kembalikan stok jika masih proses & sudah bayar
        static::deleted(function (Transaction $transaction) {
            if ((bool) $transaction->is_paid && $transaction->status === 'proses') {
                self::incrementStock($transaction);
            }
        });
    }

    // --------------------------------------------------------
    // Helper: kurangi stok sejumlah qty transaksi
    // --------------------------------------------------------
    private static function decrementStock(Transaction $transaction): void
    {
        $product = Product::find($transaction->product_id);
        if (!$product) return;

        $qty    = $transaction->qty ?? 1;
        $deduct = min($qty, $product->qty); // tidak boleh minus
        if ($deduct <= 0) return;

        $product->decrement('qty', $deduct);

        if ($product->fresh()->qty <= 0) {
            $product->update(['status' => 'disewakan']);
        }
    }

    // --------------------------------------------------------
    // Helper: tambah stok sejumlah qty transaksi
    // --------------------------------------------------------
    private static function incrementStock(Transaction $transaction): void
    {
        $product = Product::find($transaction->product_id);
        if (!$product) return;

        $qty = $transaction->qty ?? 1;
        $product->increment('qty', $qty);

        if ($product->fresh()->qty > 0) {
            $product->update(['status' => 'tersedia']);
        }
    }
}

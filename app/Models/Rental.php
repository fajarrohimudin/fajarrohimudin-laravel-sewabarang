<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_name',
        'delivery_address',
        'qty',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // --------------------------------------------------------
    // Relasi
    // --------------------------------------------------------
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // --------------------------------------------------------
    // Auto kurangi qty_available saat rental dibuat
    // --------------------------------------------------------
    protected static function booted(): void
    {
        // Saat rental baru dibuat → kurangi stok
        static::created(function (Rental $rental) {
            if ($rental->status === 'proses') {
                $rental->product->decrement('qty_available', $rental->qty);
                $rental->syncProductStatus();
            }
        });

        // Saat status berubah → kembalikan stok jika selesai/dibatalkan
        static::updated(function (Rental $rental) {
            if ($rental->wasChanged('status')) {
                $oldStatus = $rental->getOriginal('status');
                $newStatus = $rental->status;

                // Dari proses → selesai atau dibatalkan: kembalikan stok
                if ($oldStatus === 'proses' && in_array($newStatus, ['selesai', 'dibatalkan'])) {
                    $rental->product->increment('qty_available', $rental->qty);
                }

                // Dari selesai/dibatalkan → proses: kurangi stok lagi
                if (in_array($oldStatus, ['selesai', 'dibatalkan']) && $newStatus === 'proses') {
                    $rental->product->decrement('qty_available', $rental->qty);
                }

                $rental->syncProductStatus();
            }
        });

        // Saat rental dihapus → kembalikan stok
        static::deleted(function (Rental $rental) {
            if ($rental->status === 'proses') {
                $rental->product->increment('qty_available', $rental->qty);
                $rental->syncProductStatus();
            }
        });
    }

    // Update status produk otomatis berdasarkan qty_available
    protected function syncProductStatus(): void
    {
        $product = $this->product->fresh();
        if ($product->qty_available <= 0) {
            $product->update(['status' => 'disewakan']);
        } else {
            $product->update(['status' => 'tersedia']);
        }
    }
}
<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @if(auth()->user()->roles === 'ADMIN')
            <!-- Total Pengguna -->
            <div style="background-color:rgb(24, 24, 24); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
            <div style="text-gray-300 margin-bottom: 8px;">Total Kategori</div>
            <div style="font-size: 32px; font-weight: bold;">
                {{ \App\Models\Category::count() }}
            </div>
        </div>
        @endif

        <!-- Total Kategori -->
        <div style="background-color:rgb(24, 24, 24); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
            <div class="text-gray-300 mb-1">Total Produk</div>
            <div class="text-3xl font-bold">
                {{ \App\Models\Product::count() }}
            </div>
        </div>

        <!-- Total Kos -->
        <div style="background-color:rgb(24, 24, 24); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
            <div class="text-gray-300 mb-1">Total Transaksi</div>
            <div class="text-3xl font-bold">
                {{ \App\Models\Transaction::count() }}
            </div>
        </div>
    </div>
</x-filament::page>

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\Testimonial;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePaymentRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class FrontController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $latest_products = Product::with('testimonials')->latest()->take(4)->get();
        $random_products = Product::with('testimonials')->inRandomOrder()->take(6)->get();
        return view('front.index', compact('categories', 'latest_products', 'random_products'));
    }

    public function category(Category $category)
    {
        session()->put('category_id', $category->id);
        return view('front.brands', compact('category'));
    }

    public function brand(Brand $brand)
    {
        $category_id = session()->get('category_id');

        if (!$category_id) {
           return redirect()->route('front.index'); 
        }

        $category = Category::find($category_id);

        $products = Product::with('testimonials')->where('brand_id', $brand->id)
        ->where('category_id', $category_id)
        ->latest()
        ->get();

        // buat back
        $category = Category::where('id', $category_id)
        ->first();
        
        return view('front.gadgets', compact('brand', 'products', 'category'));
    }

    public function details(Product $product)
    {
        // buat back
        $brand = Brand::where('id', $product->brand_id)
        ->first();

        return view('front.details', compact('product', 'brand'));
    }

    public function booking(Product $product)
    {
        $stores = Store::all();
        return view('front.booking', compact('product', 'stores'));
    }

    public function booking_save(StoreBookingRequest $request, Product $product)
    {
        session()->put('product_id', $product->id);

        $bookingData = $request->only(['duration', 'started_at', 'store_id', 'delivery_type', 'address', 'qty']);
        session($bookingData);
        return redirect()->route('front.checkout', $product->slug);
    }

    public function checkout(Product $product)
    {
        $duration = session('duration');
        $qty      = session('qty', 1);

        $price = $product->price;

        $subTotal = $price * $duration;
        $grandTotal = $subTotal;

        return view('front.checkout', compact('product', 'subTotal', 'grandTotal', 'price', 'duration', 'qty'));
    }

    public function checkout_store(StorePaymentRequest $request)
    {
        $bookingData = session()->only(['duration', 'started_at', 'store_id', 'delivery_type', 'address', 'product_id']);
        $duration = (int) $bookingData['duration'];
        $qty = (int) ($bookingData['qty'] ?? 1);
        $startedDate = Carbon::parse($bookingData['started_at']);

        $productDetails = Product::find($bookingData['product_id']);
        if (!$productDetails) {
            return redirect()->back()->withErrors(['product_id' => 'Product tidak ada.']);
        }

        // ✅ Cek stok sebelum proses checkout
        if ($productDetails->qty < $qty) {
            return redirect()->back()->withErrors([
                'qty' => "Stok tidak cukup. Tersedia: {$productDetails->qty} unit."
            ]);
        }

        $price = $productDetails->price;
        $subTotal = $price * $duration * $qty;
        $grandTotal = $subTotal;

        $bookingTransactionId = null;

        DB::transaction(function() use ($request, &$bookingTransactionId, $duration, $qty, $bookingData, $grandTotal, $productDetails, $startedDate){
            $validated = $request->validated();

            if($request->hasFile('proof')){
                $proofPath = $request->file('proof')->store('proofs', 'public');
                $validated['proof'] = $proofPath;
            }

            $endedDate = $startedDate->copy()->addDays($duration);

            $validated['started_at']          = $startedDate;
            $validated['ended_at']            = $endedDate;
            $validated['duration']            = $duration;
            $validated['qty']                 = $qty; 
            $validated['total_amount']        = $grandTotal;
            $validated['store_id']            = $bookingData['store_id'];
            $validated['product_id']          = $productDetails->id;
            $validated['delivery_type']       = $bookingData['delivery_type'];
            $validated['address']             = $bookingData['address'];
            $validated['is_paid']             = false;
            $validated['trx_id']              = Transaction::generateUniqueTrxId();
            $validated['user_id']             = Auth::user()->id;
            $validated['transaction_status']  = 'IN_CART';
            $validated['status']              = 'proses'; // ✅ set default status rental

            $newBooking = Transaction::create($validated);
            $bookingTransactionId = $newBooking->id;
        });

        return redirect()->route('front.payment', $bookingTransactionId);
    }

    public function payment(Request $request, $id)
    {
        try {
            // Cari transaksi berdasarkan ID
            $transaction = Transaction::with(['product'])->findOrFail($id);

            // Cek jika transaction_url sudah ada, berarti sudah pernah redirect
            if ($transaction->transaction_url) {
                // Jika sudah ada URL transaksi, kita bisa langsung redirect ke halaman yang sudah ada
                return redirect()->away($transaction->transaction_url);
            }

            // Update status transaksi menjadi PENDING
            $transaction->transaction_status = 'PENDING';
            $transaction->save();

            $user = auth()->user();

            // Set konfigurasi Midtrans
            Config::$serverKey = config('midtrans.serverKey');
            Config::$isProduction = config('midtrans.isProduction');
            Config::$isSanitized = config('midtrans.isSanitized');
            Config::$is3ds = config('midtrans.is3ds');

            // Buat array untuk dikirim ke Midtrans
            $midtrans_params = [
                'transaction_details' => [
                    'order_id' => 'MIDTRANS-' . $transaction->id,
                    'gross_amount' => (int) $transaction->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'enabled_payments' => ['gopay', 'bank_transfer'],
                'vtweb' => [],
            ];

            // Ambil halaman payment Midtrans
            $paymentUrl = Snap::createTransaction($midtrans_params)->redirect_url;

            // Simpan URL transaksi ke dalam kolom transaction_url
            $transaction->transaction_url = $paymentUrl;
            $transaction->save();

            // Redirect ke halaman Midtrans
            return redirect()->away($paymentUrl);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function success_booking(Transaction $transaction)
    // {
    //     return view('front.success_booking', compact('transaction'));
    // }

    public function transactions()
    {
        $transactions = Transaction::where('user_id', Auth::user()->id)
        ->orderBy('id', 'DESC')
        ->get();

        return view('front.my_booking', compact('transactions'));
    }

    public function transactions_details($id)
    {
        $details = Transaction::with(['store', 'product', 'testimonials'])->findOrFail($id);

        $productPrice = $details->product->price ?? 0;
        $duration = $details->duration ?? 1;

        $subTotal = $productPrice * $duration;

        session()->put([
            'product_id' => $details->product_id,
            'transaction_id' => $id,
        ]);

        // dd($details->testimonials->transaction_id);

        return view('front.transaction_details', compact('details', 'productPrice', 'duration', 'subTotal'));
    }

    public function testimonials()
    {
        $data = session()->only(['product_id', 'transaction_id']);
        $product_id = $data['product_id'];
        $transaction_id = $data['transaction_id'];
        // dd($transaction_id);

        return view('front.testimonials', compact('product_id', 'transaction_id'));
    }

    public function testimonials_send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|exists:users,id',
            'transaction_id' => 'required|exists:transactions,id',
            'product_id'     => 'required|exists:products,id',
            'rating'         => 'required|integer|min:1|max:5',
            'ulasan'         => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        Testimonial::create([
            'user_id'        => $request->user_id,
            'transaction_id' => $request->transaction_id,
            'product_id'     => $request->product_id,
            'rating'         => $request->rating,
            'ulasan'         => $request->ulasan,
        ]);

        return redirect()->route('front.index')->with('success', 'Terima kasih! Ulasan kamu berhasil dikirim.');
    }

    public function testimonials_show(Testimonial $testimonial)
    {
        return view('front.show_testimonials', compact('testimonial'));
    }


}
 
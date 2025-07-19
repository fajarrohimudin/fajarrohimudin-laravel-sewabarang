<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Sukses</title>
</head>
<body>
    <h2>Halo {{ $data->user->name }},</h2>
    <p>Terima kasih, pembayaran Anda untuk transaksi <strong>{{ $data->trx_id }}</strong> telah berhasil.</p>
    <p>Detail Transaksi Pesanan:</p>
    <ul>
        <li>Nama barang: {{ $data->product->name }}</li>
        <li>Status pembayaran: {{ $data->transaction_status }}</li>
        <li>Total: Rp {{ number_format($data->total_amount) }}</li>
    </ul>
</body>
</html>

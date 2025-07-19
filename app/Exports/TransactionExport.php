<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Transaction::with(['product', 'store'])->get()->map(function ($trx) {
            return [
                'name' => $trx->name,
                'trx_id' => $trx->trx_id,
                'total_amount' => $trx->total_amount,
                'product' => $trx->product?->name,
                'store' => $trx->store?->name,
                'is_paid' => $trx->is_paid ? 'Paid' : 'Not Paid',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Transaction ID',
            'Total Amount',
            'Product',
            'Store',
            'Payment Status',
        ];
    }
}

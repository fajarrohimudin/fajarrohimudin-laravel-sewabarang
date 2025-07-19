<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Transaksi'; 
    protected static ?string $pluralModelLabel = 'Transaksi'; 
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Kelola Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('trx_id')
                ->required()
                ->maxLength(255),

                Forms\Components\Textarea::make('address')
                ->required()
                ->maxLength(1024),

                Forms\Components\TextInput::make('total_amount')
                ->required()
                ->numeric()
                ->prefix('IDR'),

                Forms\Components\TextInput::make('duration')
                ->required()
                ->numeric()
                ->prefix('Days')
                ->maxValue(255),

                Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->required(),

                Forms\Components\Select::make('store_id')
                ->relationship('store', 'name')
                ->searchable()
                ->preload()
                ->required(),

                Forms\Components\DatePicker::make('started_at')
                ->required(),

                Forms\Components\DatePicker::make('ended_at')
                ->required(),

                Forms\Components\Select::make('delivery_type')
                ->options([
                    'pickup' => 'Pickup Store',
                    'home_delivery' => 'Home Delivery'
                ])
                ->required(),

                Forms\Components\Select::make('is_paid')
                ->options([
                    true => 'Paid',
                    false => 'Not Paid'
                ])
                ->required(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),

                Tables\Columns\TextColumn::make('trx_id')
                ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                ->numeric()
                ->prefix('Rp '),

                Tables\Columns\IconColumn::make('is_paid')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Sudah Bayar?'),

                Tables\Columns\TextColumn::make('product.name'),
            ])
            ->headerActions([
                ExportAction::make('export_all')
                    ->label('Export Transaksi')
                    ->exports([
                        ExcelExport::make()
                            ->withFilename('transaksi-' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('name')
                                    ->heading('Nama'),
                                Column::make('trx_id')
                                    ->heading('Kode Transaksi'),
                                Column::make('total_amount')
                                    ->heading('Total Transaksi'),
                                Column::make('product.name')
                                    ->heading('Nama Produk'),
                                Column::make('store.name')
                                    ->heading('Toko'),
                                Column::make('started_at')
                                    ->heading('Tanggal Sewa'),
                                Column::make('ended_at')
                                    ->heading('Tanggal Selesai Sewa'),
                                Column::make('is_paid')
                                    ->heading('Status Pembayaran')
                                    ->formatStateUsing(fn ($state) => $state ? 'Sudah bayar' : 'Belum bayar'),

                            ])
                            ->fromTable(),
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

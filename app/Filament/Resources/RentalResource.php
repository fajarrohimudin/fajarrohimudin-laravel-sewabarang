<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class RentalResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Penyewaan';
    protected static ?string $pluralModelLabel = 'Penyewaan';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Kelola Penyewaan';

    public static function canCreate(): bool
    {
        return false;
    }

    // ✅ Hanya tampilkan transaksi yang sudah bayar
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_paid', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Customer')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Customer')
                            ->disabled(),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('No. Telepon')
                            ->disabled(),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('trx_id')
                            ->label('Kode Transaksi')
                            ->disabled(),

                        Forms\Components\Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Bayar')
                            ->prefix('IDR')
                            ->disabled(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Durasi Sewa')
                            ->suffix('Hari')
                            ->disabled(),

                        Forms\Components\DatePicker::make('started_at')
                            ->label('Tanggal Mulai')
                            ->disabled(),

                        Forms\Components\DatePicker::make('ended_at')
                            ->label('Tanggal Selesai')
                            ->disabled(),

                        Forms\Components\Select::make('delivery_type')
                            ->label('Tipe Pengiriman')
                            ->options([
                                'pickup'        => 'Pickup Store',
                                'home_delivery' => 'Home Delivery',
                            ])
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Status Penyewaan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'proses'     => 'Proses',
                                'selesai'    => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_paid', true))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trx_id')
                    ->label('Kode Transaksi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Tgl Mulai')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Tgl Selesai')
                    ->date('d M Y'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Penyewaan')
                    ->colors([
                        'warning' => 'proses',
                        'success' => 'selesai',
                        'danger'  => 'dibatalkan',
                    ]),
            ])
            ->headerActions([
                // ExportAction::make('export_rental')
                //     ->label('Export Penyewaan')
                //     ->exports([
                //         ExcelExport::make()
                //             ->withFilename('penyewaan-' . date('Y-m-d'))
                //             ->withColumns([
                //                 Column::make('name')->heading('Nama Customer'),
                //                 Column::make('trx_id')->heading('Kode Transaksi'),
                //                 Column::make('product.name')->heading('Produk'),
                //                 Column::make('total_amount')->heading('Total Bayar'),
                //                 Column::make('duration')->heading('Durasi (Hari)'),
                //                 Column::make('started_at')->heading('Tanggal Mulai'),
                //                 Column::make('ended_at')->heading('Tanggal Selesai'),
                //                 Column::make('delivery_type')
                //                     ->heading('Tipe Pengiriman')
                //                     ->formatStateUsing(fn ($state) => $state === 'pickup' ? 'Pickup Store' : 'Home Delivery'),
                //                 Column::make('status')
                //                     ->heading('Status Penyewaan')
                //                     ->formatStateUsing(fn ($state) => ucfirst($state)),
                //             ])
                //             ->fromTable(),
                //     ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'proses'     => 'Proses',
                        'selesai'    => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentals::route('/'),
            'edit'  => Pages\EditRental::route('/{record}/edit'),
        ];
    }
}
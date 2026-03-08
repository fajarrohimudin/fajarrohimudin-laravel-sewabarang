<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $modelLabel = 'Inventaris';
    protected static ?string $pluralModelLabel = 'Inventaris';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Kelola Inventaris';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Barang')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Kursi Lipat, Meja, Tali'),

                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('contoh: pcs, set, buah'),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->maxLength(1024)
                            ->placeholder('Deskripsi tambahan tentang barang ini'),
                    ])->columns(2),

                Forms\Components\Section::make('Stok & Status')
                    ->schema([
                        Forms\Components\TextInput::make('qty_total')
                            ->label('Total Stok')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Jumlah total barang yang dimiliki'),

                        Forms\Components\TextInput::make('qty_available')
                            ->label('Stok Tersedia')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Jumlah barang yang siap digunakan'),

                        Forms\Components\TextInput::make('qty_damaged')
                            ->label('Stok Rusak')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Jumlah barang yang rusak'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'tersedia' => 'Tersedia',
                                'rusak'    => 'Rusak',
                                'hilang'   => 'Hilang',
                            ])
                            ->default('tersedia')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('qty_total')
                    ->label('Total Stok')
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty_available')
                    ->label('Tersedia')
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty_damaged')
                    ->label('Rusak')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'tersedia',
                        'danger'  => 'rusak',
                        'warning' => 'hilang',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'rusak'    => 'Rusak',
                        'hilang'   => 'Hilang',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
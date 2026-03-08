<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class InventoryResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Inventaris';
    protected static ?string $pluralModelLabel = 'Inventaris';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Kelola Inventaris';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->disabled(),

                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah Stok')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Update Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'tersedia'    => 'Tersedia',
                                'disewakan'   => 'Sedang Disewakan',
                                'maintenance' => 'Maintenance',
                                'rusak'       => 'Rusak',
                                'hilang'      => 'Hilang',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'tersedia',
                        'warning' => 'disewakan',
                        'gray'    => 'maintenance',
                        'danger'  => fn($state) => in_array($state, ['rusak', 'hilang']),
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'tersedia'    => 'Tersedia',
                        'disewakan'   => 'Sedang Disewakan',
                        'maintenance' => 'Maintenance',
                        'rusak'       => 'Rusak',
                        'hilang'      => 'Hilang',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Status'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'edit'  => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\PhotosRelationManager;
use App\Models\Product;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Kelola Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('about')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(1024),

                        Forms\Components\TextInput::make('price')
                            ->label('Harga Sewa')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),

                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Foto Utama')
                            ->required()
                            ->image(),
                    ])->columns(2),

                Forms\Components\Section::make('Stok & Status')
                    ->schema([
                        Forms\Components\TextInput::make('qty')
                            ->label('Total Stok')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->helperText('Jumlah total unit yang dimiliki'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'tersedia'   => 'Tersedia',
                                'disewakan'  => 'Sedang Disewakan',
                                'maintenance'=> 'Maintenance',
                            ])
                            ->default('tersedia')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Kategori & Brand')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('brand_id', null);
                            }),

                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->options(function (callable $get) {
                                $categoryId = $get('category_id');
                                if ($categoryId) {
                                    return Brand::whereHas('brandCategories', function ($query) use ($categoryId) {
                                        $query->where('category_id', $categoryId);
                                    })->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Foto'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand'),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Total Stok'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'tersedia',
                        'warning' => 'disewakan',
                        'danger'  => 'maintenance',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'tersedia'    => 'Tersedia',
                        'disewakan'   => 'Sedang Disewakan',
                        'maintenance' => 'Maintenance',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name'),
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

    public static function getRelations(): array
    {
        return [
            PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
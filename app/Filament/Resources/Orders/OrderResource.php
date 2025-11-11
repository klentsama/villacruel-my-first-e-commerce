<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\AddressRelationManager;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\Product;
use BackedEnum;
use Dom\Text;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()->schema([
                        Section::make('Order Information')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Customer')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('payment_method')
                                    ->options([
                                        'credit_card' => 'Credit Card',
                                        'paypal' => 'PayPal',
                                        'bank_transfer' => 'Bank Transfer',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                ToggleButtons::make('status')
                                ->inline()
                                ->default('new ')
                                ->required()
                                ->options([
                                    'new' => 'New',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'canceled' => 'Canceled',
                                ])
                                ->colors([
                                    'new' => 'info',
                                    'processing' => 'warning',
                                    'shipped' => 'success',
                                    'delivered' => 'success',
                                    'canceled' => 'danger',
                                ])
                                ->icons([
                                    'new' => 'heroicon-m-sparkles',
                                    'processing' => 'heroicon-m-arrow-path',
                                    'shipped' => 'heroicon-m-truck',
                                    'delivered' => 'heroicon-m-check-badge',
                                    'canceled' => 'heroicon-m-x-circle',
                                ]),

                                Select::make('currency')
                                    ->options([
                                        'INR' => 'INR',
                                        'USD' => 'USD',
                                        'EUR' => 'EUR',
                                        'GBP' => 'GBP',
                                    ])
                                    ->default('USD')
                                    ->required(),

                                Select::make('shipping_method')
                                    ->options([
                                        'fedex' => 'FedEx',
                                        'ups' => 'UPS',
                                        'amazon' => 'Amazon',
                                    ]),

                                Textarea::make('notes')
                                    ->columnSpanFull()
                            ])->columns(2),

                            Section::make('Order Items')
                                ->schema([
                                    Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name')      
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()   
                                            ->columnSpan(4)
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $set) =>$set('unit_amount', Product::find($state)?->price ?? 0))
                                            ->afterStateUpdated(fn($state, $set) =>$set('total_amount', Product::find($state)?->price ?? 0)),
                                            
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, $get, $set) => $set('total_amount', $state * $get('unit_amount')))
                                            ->columnSpan(2),

                                        TextInput::make('unit_amount')
                                            ->numeric()
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(3),

                                        TextInput::make('total_amount')
                                            ->numeric()
                                            ->required()
                                            ->dehydrated()
                                            ->columnSpan(3),
                                    ])->columns(12),

                                    Placeholder::make('gran_total_placeholder')
                                        ->label('Grand Total')
                                        ->content(function($get, $set) {
                                            $total =0;
                                            if(!$repeaters = $get('items')){
                                                return $total;
                                            }
                                            foreach($repeaters as $key => $repeater){
                                                $total += $get("items.{$key}.total_amount");
                                            }
                                            return Number::currency($total, 'USD');
                                        }),

                                        Hidden::make('grand_total')
                                        ->default(0)
                                ])
                    ])->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('user.name')
                ->label('Customer')
                ->searchable()
                ->sortable(),

            TextColumn::make('grand_total')
                ->label('Grand Total')
                ->numeric()
                ->sortable(),

            TextColumn::make('payment_method')
                ->searchable()
                ->sortable(), 
            
            TextColumn::make('payment_status')
                 ->searchable()
                 ->sortable(),

            TextColumn::make('currency')
                 ->searchable()
                 ->sortable(),
            
            TextColumn::make('shipping_method')
                ->searchable()
                ->sortable(), 

            SelectColumn::make('status')
                ->options([
                    'new' => 'New',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'canceled' => 'Canceled',
                ])
                ->searchable()
                ->sortable(),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault:true),

            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault:true),
        ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),       
                ])
            ]);
    }

    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}

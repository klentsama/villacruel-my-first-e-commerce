<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function Pest\Laravel\delete;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            TextInput::make('name')->required(),

            TextInput::make('email')
                ->label('Email Address')
                ->email()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->required(),

            DateTimePicker::make('email_verified_at')
                ->label('Email Verified At')
                ->default(now()),

            TextInput::make('password')
                ->password()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn ($livewire): bool => $livewire instanceof CreateRecord),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                    TextColumn::make('email_verified_at')
                        ->dateTime()
                        ->sortable(),

                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable(),
            ])
            ->recordActions([
                    ActionGroup::make([
                        ViewAction::make(),
                        EditAction::make(),
                        DeleteAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}

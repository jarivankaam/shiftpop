<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftsResource\Pages;
use App\Models\Shift;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Resources\Table;

class ShiftsResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Plannen';
    protected static ?string $label = 'Planning';
    protected static ?string $pluralLabel = 'Planningen';

    public static function form(Form|Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Start Time')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_time')
                    ->label('End Time')
                    ->required(),
            ]);
    }

    public static function table(Table|Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('start_time')->label('Start')->dateTime('D, M j Y H:i'),
                Tables\Columns\TextColumn::make('end_time')->label('End')->dateTime('D, M j Y H:i'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Created'),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define related managers if needed (e.g., HasMany requests)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShifts::route('/create'),
            'edit'   => Pages\EditShifts::route('/{record}/edit'),
        ];
    }
}

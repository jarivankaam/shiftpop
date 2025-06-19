<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\RequestsResource\Pages;
use App\Models\Requests;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RequestsResource extends Resource
{
    protected static ?string $model = Requests::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = 'Vrijvragen of Ruilen';
    protected static ?string $pluralModelLabel = 'Requests';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Aanvraag Details')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Aanvraag Type')
                        ->options([
                            'ziek' => 'Ziek',
                            'vakantie' => 'Vakantie',
                            'vrije_dag' => 'Vrije Dag(en)',
                            'overnemen' => 'Overnemen',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\Select::make('shift_id')
                        ->label('Over te nemen Dienst')
                        ->relationship(
                            name: 'shift',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn ($query) => $query
                                ->with('user')
                                ->where('user_id', '!=', auth()->id()) // ✅ Exclude own shifts
                        )
                        ->getOptionLabelFromRecordUsing(function ($record) {
                            $userName = $record->user->name ?? 'Onbekend';
                            $start = \Carbon\Carbon::parse($record->start_time)->format('Y-m-d H:i');
                            $end = \Carbon\Carbon::parse($record->end_time)->format('H:i');
                            return "{$userName} - {$start} → {$end}";
                        })
                        ->searchable()
                        ->preload()
                        ->hidden(fn (Forms\Get $get) => $get('type') !== 'overnemen'),

                    Forms\Components\DatePicker::make('requested_date')
                        ->label('Datum')
                        ->required(fn (Forms\Get $get) => $get('type') !== 'overnemen')
                        ->hidden(fn (Forms\Get $get) => $get('type') === 'overnemen'),

                    Forms\Components\Textarea::make('Reason')
                        ->label('Reden')
                        ->rows(3)
                        ->maxLength(500),

                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->sortable()->label('Type'),
                Tables\Columns\TextColumn::make('requested_date')->date()->label('Date'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Submitted'),
            ])
            ->filters([
                // You can add filters here if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequests::route('/create'),
            'edit' => Pages\EditRequests::route('/{record}/edit'),
        ];
    }

    /**
     * Scope the resource to the current user
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
}

<?php

namespace App\Filament\dev\Resources;

use App\Filament\Resources\RequestsResource\Pages;
use App\Models\Requests;
use App\Models\Shift;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class RequestsResource extends Resource
{
    protected static ?string $model = Requests::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Aanvragen';
    protected static ?string $label = 'Vrij';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralLabel = 'Vrij';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->disabled(),

            Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'sick' => 'Sick',
                    'vacation' => 'Vacation',
                    'personal' => 'Personal',
                    'takeover' => 'Overnemen',
                ])
                ->disabled(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->required(),

            Forms\Components\Textarea::make('Reason')
                ->label('Reason')
                ->disabled(),

            Forms\Components\Select::make('shift_id')
                ->label('Shift')
                ->relationship('shift', 'id')
                ->getOptionLabelFromRecordUsing(function ($record) {
                    $userName = $record->user->name ?? 'Unknown';
                    $start = \Carbon\Carbon::parse($record->start_time)->format('Y-m-d H:i');
                    $end = \Carbon\Carbon::parse($record->end_time)->format('H:i');
                    return "{$userName} - {$start} → {$end}";
                })
                ->searchable()
                ->disabled(),

            Forms\Components\DatePicker::make('requested_date')
                ->label('Requested Date')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('requested_date')->label('Date')->date(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Submitted')->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(), // This is where you approve/reject
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRequests::route('/'),
            'edit'   => Pages\EditRequests::route('/{record}/edit'),
            'create' => Pages\CreateRequests::route('/create'), // optional
        ];
    }
}

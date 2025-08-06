<?php

namespace App\Filament\CentralPanel\Resources;

use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use App\Models\Tenant;
use App\Filament\CentralPanel\Resources\TenantResource\Pages;

class TenantResource extends Resource
{




protected static ?string $model = Tenant::class;


    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Tenants';
   protected static ?string $label = 'Tenant';
protected static ?string $pluralLabel = 'Tenants';


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('id')->required()->label('Tenant ID'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('domains.domain')->label('Domain'),

        ])            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}


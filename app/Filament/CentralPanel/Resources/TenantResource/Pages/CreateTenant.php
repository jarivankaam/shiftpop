<?php

namespace App\Filament\CentralPanel\Resources\TenantResource\Pages;

use App\Filament\CentralPanel\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;


class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
    protected function afterCreate(): void
    {
         /** @var Tenant $tenant */
        $tenant = $this->record;

        // 🧠 Add domain using the slug
        $tenant->domains()->create([
            'domain' => "{$tenant->id}.shiftpop.test", // or use a slug field if you have one
        ]);
       $tenant->run(function () {
            Artisan::call('tenants:migrate', [
                '--tenants' => [$this->record->id],
            ]);
    });
    }
}

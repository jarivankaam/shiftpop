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
        $tenant = $this->record; // ✅ this is already saved and has a valid ID

        // ✅ Now it's safe to attach a domain
      $tenant = Tenant::create([
    'id' => $tenant->id, // or use UUIDs
]);

$tenant->domains()->create([
    'domain' => "{$tenant->id}.shi",
]);

        // Optionally run tenant migrations
        $tenant->run(function () {
            Artisan::call('tenants:migrate', ['--force' => true]);
        });
    }
}

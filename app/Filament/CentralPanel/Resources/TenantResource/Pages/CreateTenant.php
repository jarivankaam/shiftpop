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
        $tenant = $this->data; // ✅ This is the tenant that was just created

        // Create domain (use real domain!)
        $tenant->domains()->create([
            'domain' => "{$tenant->id}.shiftpop.eu", // ✅ Replace "shi" with full domain
        ]);

        // Run tenant migrations
        $tenant->run(function () {
            Artisan::call('tenants:migrate', ['--force' => true]);
        });
    }
}

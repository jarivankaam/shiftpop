<?php

namespace App\Filament\CentralPanel\Resources\TenantResource\Pages;

use App\Filament\CentralPanel\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;

    use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;


protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['tenant_id'] = (string) Str::uuid(); // 👈 Generate UUID before creation
    $data['id'] = (string) Str::uuid(); // 👈 Generate UUID before creation
    return $data;
}


       protected function afterCreate(): void
    {

       /** @var Tenant $tenant */
        $tenant = $this->record; // ✅ This is the tenant that was just created
        // Create domain (use real domain!)

        // dd($tenant->tenant_id);
        $tenant->domains()->create([
            'domain' => "{$tenant->slug}.shiftpop.eu", // ✅ Replace "shi" with full domain
            'tenant_id' => "$tenant->tenant_id",
        ]);

        // Run tenant migrations
        $tenant->run(function () {
            Artisan::call('tenants:migrate', ['--force' => true]);
        });
    }

}

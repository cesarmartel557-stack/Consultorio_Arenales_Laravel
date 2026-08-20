<?php

namespace App\Filament\Resources\HealthInsuranceResource\Pages;

use App\Filament\Resources\HealthInsuranceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHealthInsurances extends ListRecords
{
    protected static string $resource = HealthInsuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

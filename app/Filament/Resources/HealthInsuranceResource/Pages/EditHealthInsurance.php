<?php

namespace App\Filament\Resources\HealthInsuranceResource\Pages;

use App\Filament\Resources\HealthInsuranceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHealthInsurance extends EditRecord
{
    protected static string $resource = HealthInsuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\HomePageResource\Pages;

use App\Filament\Resources\HomePageResource;
use App\Models\HomePage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomePages extends ListRecords
{
    protected static string $resource = HomePageResource::class;

    public function mount(): void
    {
        // Si ya existe el registro, ir directo al formulario de edición
        $page = HomePage::first();
        if ($page) {
            $this->redirect(HomePageResource::getUrl('edit', ['record' => $page]));

            return;
        }

        // Si no existe, ir a crear
        $this->redirect(HomePageResource::getUrl('create'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

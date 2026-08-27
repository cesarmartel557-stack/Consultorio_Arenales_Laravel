<?php

namespace App\Filament\Pages;

use App\Models\Specialty;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class MiPerfil extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Mi perfil';

    protected static ?string $title = 'Mi perfil';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.mi-perfil';

    /** Datos del formulario enlazados con Livewire */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        // Solo médicos con ficha vinculada ven esta página.
        return auth()->user()?->doctor !== null;
    }

    public function mount(): void
    {
        $doctor = auth()->user()->doctor->load(['specialties']);

        $this->form->fill([
            'title' => $doctor->title,
            'license' => $doctor->license,
            'headline' => $doctor->headline,
            'bio' => $doctor->bio,
            'photo' => $doctor->photo,
            'specialties' => $doctor->specialties->pluck('id')->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Foto de Perfil')
                    ->description('Mostrá tu mejor perfil a los pacientes.')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->directory('doctors')
                            ->disk('public')
                            ->imageEditor()
                            ->avatar()
                            ->alignCenter(),
                    ]),

                Forms\Components\Section::make('Información Profesional')
                    ->description('Completá tu información pública.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título / Tratamiento')
                            ->placeholder('Ej: Dr., Dra., Lic.')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('license')
                            ->label('Matrícula profesional')
                            ->placeholder('Ej: MN 105432')
                            ->maxLength(120),

                        Forms\Components\TextInput::make('headline')
                            ->label('Título / Especialidad breve')
                            ->placeholder('Ej: Especialista en Ginecología y Obstetricia')
                            ->maxLength(180)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('specialties')
                            ->label('Especialidades')
                            ->options(Specialty::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biografía o Presentación')
                            ->placeholder('Contale a los pacientes un poco sobre tu experiencia o indicaciones importantes...')
                            ->rows(5)
                            ->maxLength(1000)
                            ->helperText('Se respetan los saltos de línea y espacios al mostrarse en la web.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $doctor = auth()->user()->doctor;
        $state = $this->form->getState();

        // Eliminar foto anterior si se subió una nueva o se eliminó
        if ($doctor->photo && $doctor->photo !== $state['photo']) {
            Storage::disk('public')->delete($doctor->photo);
        }

        // Actualizar datos del modelo
        $doctor->update([
            'title' => $state['title'],
            'license' => $state['license'],
            'headline' => $state['headline'] ?? null,
            'bio' => $state['bio'],
            'photo' => $state['photo'],
        ]);

        // Sincronizar especialidades
        $doctor->specialties()->sync($state['specialties'] ?? []);

        Notification::make()
            ->title('Perfil guardado')
            ->body('Tus cambios ya son visibles en la plataforma.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->submit('save'),
        ];
    }
}

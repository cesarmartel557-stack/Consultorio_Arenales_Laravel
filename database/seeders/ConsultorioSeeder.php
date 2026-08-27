<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\HealthInsurance;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsultorioSeeder extends Seeder
{
    public function run(): void
    {
        $specialties = collect([
            ['name' => 'Ginecología', 'icon' => 'assets/images/icon-gineco.webp'],
            ['name' => 'Obstetricia', 'icon' => 'assets/images/icon-obstetricia.webp'],
            ['name' => 'Fertilidad', 'icon' => 'assets/images/icon-fertilidad.webp'],
            ['name' => 'Mastología', 'icon' => 'assets/images/icon-mastologia.webp'],
            ['name' => 'Nutrición', 'icon' => 'assets/images/icon-nutricion.webp'],
            ['name' => 'Pediatría', 'icon' => 'assets/images/icon-gineco.webp'],
        ])->mapWithKeys(function (array $data, int $index) {
            $specialty = Specialty::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [...$data, 'sort_order' => $index, 'is_active' => true],
            );

            return [$data['name'] => $specialty];
        });

        $insurances = collect([
            'OSDE' => 'assets/images/prepagas/osde-logo.webp',
            'Swiss Medical' => 'assets/images/prepagas/swiss-medical.svg',
            'Galeno' => 'assets/images/prepagas/logo-vector-galeno.webp',
            'Omint' => 'assets/images/prepagas/omint.svg',
            'Medicus' => 'assets/images/prepagas/medicus.svg',
            'Medifé' => 'assets/images/prepagas/medife.svg',
            'Accord Salud' => 'assets/images/prepagas/accord-salud.svg',
            'Sancor Salud' => 'assets/images/prepagas/sancor-salud.svg',
            'Prevención Salud' => 'assets/images/prepagas/prevencion-salud.svg',
            'Hospital Alemán' => 'assets/images/prepagas/hospital-aleman.svg',
            'Hospital Británico' => 'assets/images/prepagas/hospital-britanico.svg',
            'Federada Salud' => 'assets/images/prepagas/federada-salud.svg',
            'Particular' => null,
        ])->mapWithKeys(function (?string $logo, string $name) {
            static $order = 0;

            $insurance = HealthInsurance::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'logo' => $logo, 'sort_order' => $order++, 'is_active' => true],
            );

            return [$name => $insurance];
        });

        $doctors = [
            [
                'title' => 'Dr.', 'name' => 'Gabriel Toledo', 'license' => 'MN 92785 | MP 446896',
                'headline' => 'Especialista en Ginecología, Obstetricia y Patología mamaria',
                'photo' => 'assets/images/gabriel-toledo.webp',
                'specialties' => ['Ginecología', 'Obstetricia', 'Mastología'],
                'insurances' => ['Sancor Salud', 'OSDE', 'Omint', 'Particular'],
                'schedules' => [[2, '14:00', '19:00'], [4, '14:00', '19:00'], [5, '09:00', '13:00']],
            ],
            [
                'title' => 'Dra.', 'name' => 'Mariana Albrizio', 'license' => null,
                'headline' => 'Especialista en Fertilidad',
                'photo' => 'assets/images/mariana-albrizio.webp',
                'specialties' => ['Fertilidad'],
                'insurances' => ['Accord Salud', 'Swiss Medical', 'Prevención Salud', 'Particular'],
                'schedules' => [[2, '10:00', '15:00']],
            ],
            [
                'title' => 'Dr.', 'name' => 'Humberto Giambastiani', 'license' => 'MN 43029',
                'headline' => 'Ginecología y Obstetricia',
                'photo' => 'assets/images/humberto-giambastiani.webp',
                'specialties' => ['Ginecología', 'Obstetricia'],
                'insurances' => ['Galeno', 'OSDE', 'Medicus', 'Particular'],
                'schedules' => [[2, '09:00', '13:00'], [5, '15:00', '19:00']],
            ],
            [
                'title' => 'Dr.', 'name' => 'Mariano Martinotti', 'license' => 'MN 111797',
                'headline' => 'Tocoginecólogo',
                'photo' => 'assets/images/mariano-martinotti.webp',
                'specialties' => ['Ginecología', 'Obstetricia'],
                'insurances' => ['Hospital Alemán', 'Medifé', 'Swiss Medical', 'Particular'],
                'schedules' => [[3, '09:00', '14:00']],
            ],
            [
                'title' => 'Dra.', 'name' => 'Silvina Vulcano', 'license' => 'MN 8321',
                'headline' => 'Especialista en Ginecología y Cannabis medicinal',
                'photo' => 'assets/images/silvina-vulcano.webp',
                'specialties' => ['Ginecología'],
                'insurances' => ['Hospital Británico', 'Federada Salud', 'Particular'],
                'schedules' => [[1, '10:00', '16:00'], [4, '09:00', '13:00']],
            ],
            [
                'title' => 'Dra.', 'name' => 'Claudia Krasnapolsky', 'license' => 'MN 105432',
                'headline' => 'Ginecología, Adolescencia y Medicina Estética',
                'photo' => 'assets/images/claudia-krasnapolsky.webp',
                'specialties' => ['Ginecología'],
                'insurances' => ['Prevención Salud', 'Omint', 'Medifé', 'Particular'],
                'schedules' => [[3, '14:00', '19:00']],
            ],
            [
                'title' => 'Dra.', 'name' => 'Natalia Capeluto', 'license' => 'MN 105432',
                'headline' => 'Especialista en Pediatría y medicina del viajero',
                'photo' => 'assets/images/natalia-capeluto.webp',
                'specialties' => ['Pediatría'],
                'insurances' => ['Medicus', 'Galeno', 'OSDE', 'Particular'],
                'schedules' => [[3, '14:00', '19:00']],
            ],
            [
                'title' => 'Dra.', 'name' => 'Laura Bidegain', 'license' => 'MN 105432',
                'headline' => 'Especialista en Mastología',
                'photo' => null,
                'specialties' => ['Mastología'],
                'insurances' => ['Galeno', 'Swiss Medical', 'OSDE', 'Omint', 'Particular'],
                'schedules' => [[3, '14:00', '19:00']],
            ],
        ];

        foreach ($doctors as $index => $data) {
            $slug = Str::slug($data['name']);

            $user = User::updateOrCreate(
                ['email' => $slug.'@consultoriointegralarenales.com.ar'],
                [
                    'name' => trim("{$data['title']} {$data['name']}"),
                    'role' => UserRole::Doctor,
                    'password' => 'password',
                ],
            );

            $doctor = Doctor::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'name' => $data['name'],
                    'license' => $data['license'],
                    'headline' => $data['headline'],
                    'photo' => $data['photo'],
                    'slot_minutes' => 15,
                    'min_hours_notice' => 2,
                    'max_days_ahead' => 60,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );

            $doctor->specialties()->sync(
                $specialties->only($data['specialties'])->pluck('id')
            );

            $doctor->healthInsurances()->sync(
                $insurances->only($data['insurances'])->pluck('id')
            );

            $doctor->schedules()->delete();

            foreach ($data['schedules'] as [$weekday, $start, $end]) {
                $doctor->schedules()->create([
                    'weekday' => $weekday,
                    'start_time' => $start,
                    'end_time' => $end,
                    'is_active' => true,
                ]);
            }
        }

        User::updateOrCreate(
            ['email' => 'admin@consultoriointegralarenales.com.ar'],
            [
                'name' => 'Administración',
                'role' => UserRole::Admin,
                'password' => 'password',
            ],
        );
    }
}

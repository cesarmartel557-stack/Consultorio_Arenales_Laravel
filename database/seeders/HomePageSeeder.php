<?php

namespace Database\Seeders;

use App\Models\HomePage;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        HomePage::firstOrCreate(['id' => 1], [
            'hero_title' => 'Medicina especializada para acompañarte en cada etapa de tu vida.',
            'hero_description' => 'En Consultorio Integral Arenales brindamos atención médica personalizada con un equipo de profesionales especializados, tecnología de última generación y un enfoque centrado en vos y tu bienestar.',
            'hero_button_1_text' => 'Solicitar Turno',
            'hero_button_1_link' => '/profesionales',
            'hero_button_2_text' => 'Conocé nuestras especialidades',
            'hero_button_2_link' => '/especialidades',
            'feature_1_title' => 'Atención personalizada',
            'feature_1_description' => 'Cada consulta comienza escuchando tus necesidades.',
            'feature_2_title' => 'Profesionales especializados',
            'feature_2_description' => 'Un equipo médico con amplia experiencia y formación continua.',
            'feature_3_title' => 'Tecnología de última generación',
            'feature_3_description' => 'Equipamiento moderno para diagnósticos precisos y tratamientos de calidad.',
            'team_title' => 'Un equipo comprometido con tu salud',
            'team_description' => "Cada profesional de Consultorio Integral Arenales comparte una misma filosofía: brindar atención cercana, escucha activa y excelencia médica en cada consulta.\n\nNuestro trabajo interdisciplinario nos permite ofrecer un abordaje integral para acompañarte con confianza y seguridad.",
            'team_button_text' => 'Conocé a nuestro equipo',
            'team_button_link' => '/nosotros',
        ]);
    }
}

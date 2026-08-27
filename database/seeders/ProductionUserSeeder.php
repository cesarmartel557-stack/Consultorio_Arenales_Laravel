<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionUserSeeder extends Seeder
{
    /**
     * Lista de usuarios y credenciales seguras para entorno de producción.
     * Todas las contraseñas superan los 16 caracteres, con mayúsculas, minúsculas, números y símbolos.
     */
    public const USERS = [
        [
            'email' => 'admin@consultoriointegralarenales.com.ar',
            'name' => 'Administración',
            'role' => UserRole::Admin,
            'password' => 'Adm#Arenales!2026$Sec*9k',
        ],
        [
            'email' => 'gabriel-toledo@consultoriointegralarenales.com.ar',
            'name' => 'Dr. Gabriel Toledo',
            'role' => UserRole::Doctor,
            'password' => 'Tol#DrGabi!92785$Px*7m',
        ],
        [
            'email' => 'mariana-albrizio@consultoriointegralarenales.com.ar',
            'name' => 'Dra. Mariana Albrizio',
            'role' => UserRole::Doctor,
            'password' => 'Alb#DraMari!Fert$8q*2v',
        ],
        [
            'email' => 'humberto-giambastiani@consultoriointegralarenales.com.ar',
            'name' => 'Dr. Humberto Giambastiani',
            'role' => UserRole::Doctor,
            'password' => 'Gia#DrHumb!43029$Wk*4z',
        ],
        [
            'email' => 'mariano-martinotti@consultoriointegralarenales.com.ar',
            'name' => 'Dr. Mariano Martinotti',
            'role' => UserRole::Doctor,
            'password' => 'Mar#DrMari!11179$Bn*6x',
        ],
        [
            'email' => 'silvina-vulcano@consultoriointegralarenales.com.ar',
            'name' => 'Dra. Silvina Vulcano',
            'role' => UserRole::Doctor,
            'password' => 'Vul#DraSilv!8321$Jy*3p',
        ],
        [
            'email' => 'claudia-krasnapolsky@consultoriointegralarenales.com.ar',
            'name' => 'Dra. Claudia Krasnapolsky',
            'role' => UserRole::Doctor,
            'password' => 'Kra#DraClau!1054$Lt*8d',
        ],
        [
            'email' => 'natalia-capeluto@consultoriointegralarenales.com.ar',
            'name' => 'Dra. Natalia Capeluto',
            'role' => UserRole::Doctor,
            'password' => 'Cap#DraNati!Pedi$5m*1s',
        ],
        [
            'email' => 'laura-bidegain@consultoriointegralarenales.com.ar',
            'name' => 'Dra. Laura Bidegain',
            'role' => UserRole::Doctor,
            'password' => 'Bid#DraLaur!Masto$9r*5k',
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'password' => $userData['password'],
                ],
            );
        }
    }
}

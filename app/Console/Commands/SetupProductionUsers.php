<?php

namespace App\Console\Commands;

use Database\Seeders\ProductionUserSeeder;
use Illuminate\Console\Command;

class SetupProductionUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-production-users {--force : Forzar la ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura y actualiza las credenciales seguras de producción para el administrador y los médicos.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Actualizando usuarios y contraseñas de producción...');

        $seeder = new ProductionUserSeeder;
        $seeder->run();

        $this->table(
            ['Nombre', 'Email', 'Rol', 'Contraseña Segura'],
            collect(ProductionUserSeeder::USERS)->map(fn ($u) => [
                $u['name'],
                $u['email'],
                $u['role']->value,
                $u['password'],
            ])->toArray(),
        );

        $this->info('¡Usuarios y contraseñas de producción actualizados correctamente!');

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Filament\Pages\MiPerfil;
use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Resources\DoctorResource;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRejected;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function makeDoctor(string $slug, ?User $user = null): Doctor
    {
        return Doctor::create([
            'user_id' => $user?->id,
            'title' => 'Dr.',
            'name' => 'Médico '.$slug,
            'slug' => $slug,
            'slot_minutes' => 30,
            'is_active' => true,
        ]);
    }

    private function makeAppointment(Doctor $doctor, string $time = '09:00'): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'date' => today()->addDays(3)->toDateString(),
            'start_time' => $time,
            'end_time' => '09:30',
            'status' => AppointmentStatus::Pending,
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'email' => 'ana@test.com',
            'phone' => '1122334455',
        ]);
    }

    public function test_el_panel_requiere_login(): void
    {
        $this->get('/gestion')->assertRedirect();
    }

    public function test_el_admin_ve_los_turnos_de_todos_los_medicos(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $a = $this->makeAppointment($this->makeDoctor('uno'), '09:00');
        $b = $this->makeAppointment($this->makeDoctor('dos'), '10:00');

        Livewire::actingAs($admin)
            ->test(ListAppointments::class)
            ->assertCanSeeTableRecords([$a, $b]);
    }

    public function test_el_medico_solo_ve_sus_propios_turnos(): void
    {
        $user = User::factory()->create(['role' => UserRole::Doctor]);
        $mine = $this->makeAppointment($this->makeDoctor('propio', $user), '09:00');
        $other = $this->makeAppointment($this->makeDoctor('ajeno'), '10:00');

        Livewire::actingAs($user)
            ->test(ListAppointments::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_confirmar_un_turno_envia_el_mail_al_paciente(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $appointment = $this->makeAppointment($this->makeDoctor('uno'));

        Livewire::actingAs($admin)
            ->test(ListAppointments::class)
            ->callTableAction('confirm', $appointment);

        $this->assertSame(AppointmentStatus::Confirmed, $appointment->fresh()->status);
        $this->assertNotNull($appointment->fresh()->confirmed_at);
        Mail::assertSent(AppointmentConfirmed::class);
    }

    public function test_rechazar_un_turno_libera_el_horario(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $appointment = $this->makeAppointment($this->makeDoctor('uno'));

        Livewire::actingAs($admin)
            ->test(ListAppointments::class)
            ->callTableAction('reject', $appointment, ['reason' => 'Sin disponibilidad']);

        $fresh = $appointment->fresh();

        $this->assertSame(AppointmentStatus::Rejected, $fresh->status);
        $this->assertSame('Sin disponibilidad', $fresh->status_reason);
        $this->assertNull($fresh->active_slot);
        Mail::assertSent(AppointmentRejected::class);
    }

    public function test_el_medico_no_accede_a_la_gestion_de_profesionales(): void
    {
        $user = User::factory()->create(['role' => UserRole::Doctor]);

        $this->actingAs($user)
            ->get(DoctorResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_el_badge_cuenta_los_turnos_pendientes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->makeAppointment($this->makeDoctor('uno'), '09:00');
        $this->makeAppointment($this->makeDoctor('dos'), '10:00');

        $this->assertSame('2', AppointmentResource::getNavigationBadge());
    }

    public function test_el_medico_puede_editar_su_matricula_en_mi_perfil(): void
    {
        $user = User::factory()->create(['role' => UserRole::Doctor]);
        $doctor = $this->makeDoctor('perfil', $user);

        Livewire::actingAs($user)
            ->test(MiPerfil::class)
            ->fillForm([
                'title' => 'Dra.',
                'license' => 'MN 105432',
                'headline' => 'Especialista en Pediatría',
                'bio' => "Atención virtual\nConsultas por WhatsApp",
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $doctor->fresh();
        $this->assertSame('Dra.', $fresh->title);
        $this->assertSame('MN 105432', $fresh->license);
        $this->assertSame('Especialista en Pediatría', $fresh->headline);
        $this->assertSame("Atención virtual\nConsultas por WhatsApp", $fresh->bio);
    }
}

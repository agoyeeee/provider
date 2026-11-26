<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    // Tanpa database: user tidak disimpan, cukup untuk cek role.
    private function userWithRole(string $role): User
    {
        return (new User)->forceFill(['id' => 1, 'role' => $role]);
    }

    public function test_guest_and_non_admin_cannot_backup(): void
    {
        Process::fake();

        $this->get(route('admin.backup.database'))->assertRedirect(route('login'));
        $this->actingAs($this->userWithRole('user'))->get(route('admin.backup.database'))->assertForbidden();

        Process::assertNothingRan();
    }

    public function test_admin_downloads_sql_file(): void
    {
        Process::fake();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.backup.database'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_failed_dump_is_not_downloaded(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'denied', exitCode: 2)]);

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.backup.database'))
            ->assertStatus(500);
    }
}

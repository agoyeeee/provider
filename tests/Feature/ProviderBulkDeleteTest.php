<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\Tiang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return (new User)->forceFill(['id' => 1, 'role' => $role]);
    }

    private function titik(): Provider
    {
        $p = Provider::create(['kecamatan' => 'Jebres', 'kelurahan' => 'Sewu']);
        $p->tiang()->create(['n_provider' => 'Telkom']);

        return $p;
    }

    public function test_admin_deletes_only_selected_points_and_their_tiang(): void
    {
        [$a, $b, $keep] = [$this->titik(), $this->titik(), $this->titik()];

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('admin.provider.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect();

        $this->assertSame([$keep->id], Provider::pluck('id')->all());
        $this->assertSame([$keep->id], Tiang::pluck('provider_id')->all());
    }

    public function test_empty_selection_and_non_admin_delete_nothing(): void
    {
        $p = $this->titik();

        $this->delete(route('admin.provider.bulk-destroy'), ['ids' => [$p->id]])->assertRedirect(route('login'));
        $this->actingAs($this->userWithRole('user'))
            ->delete(route('admin.provider.bulk-destroy'), ['ids' => [$p->id]])->assertForbidden();
        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('admin.provider.bulk-destroy'), ['ids' => []])->assertSessionHasErrors('ids');

        $this->assertSame(1, Provider::count());
    }
}

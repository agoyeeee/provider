<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Provider::with('user');

        // Filter by provinsi
        if ($request->filled('provinsi')) {
            $query->where('provinsi', $request->provinsi);
        }

        // Filter by kota
        if ($request->filled('kota')) {
            $query->where('kota', $request->kota);
        }

        // Filter by kecamatan
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }

        // Filter by provider name
        if ($request->filled('n_provider')) {
            $query->where('n_provider', 'like', '%' . $request->n_provider . '%');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fid', 'like', '%' . $search . '%')
                    ->orWhere('provinsi', 'like', '%' . $search . '%')
                    ->orWhere('kota', 'like', '%' . $search . '%')
                    ->orWhere('kecamatan', 'like', '%' . $search . '%')
                    ->orWhere('kelurahan', 'like', '%' . $search . '%')
                    ->orWhere('n_provider', 'like', '%' . $search . '%')
                    ->orWhere('odp', 'like', '%' . $search . '%');
            });
        }

        $providers = $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'fid' => $item->fid,
                'provinsi' => $item->provinsi,
                'kota' => $item->kota,
                'kecamatan' => $item->kecamatan,
                'kelurahan' => $item->kelurahan,
                'k_ref_wil' => $item->k_ref_wil,
                'utilitas' => $item->utilitas,
                'n_provider' => $item->n_provider,
                'odp' => $item->odp,
                'sjalu_21' => $item->sjalu_21,
                'x' => $item->x,
                'y' => $item->y,
                'foto' => $item->foto,
                'foto_url' => $item->foto_url,
                'tgl_survey' => $item->tgl_survey?->format('Y-m-d'),
                'id_user' => $item->id_user,
                'user' => $item->user,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        $users = User::select('id', 'name')->get();

        // Get unique values for filter dropdowns
        $filterOptions = [
            'provinsi' => Provider::distinct()->pluck('provinsi')->filter()->values(),
            'kota' => Provider::distinct()->pluck('kota')->filter()->values(),
            'kecamatan' => Provider::distinct()->pluck('kecamatan')->filter()->values(),
            'n_provider' => Provider::distinct()->pluck('n_provider')->filter()->values(),
        ];

        return Inertia::render('Admin/ProviderView', [
            'providers' => $providers,
            'users' => $users,
            'filterOptions' => $filterOptions,
            'filters' => $request->only(['provinsi', 'kota', 'kecamatan', 'n_provider', 'search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fid' => 'nullable|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'k_ref_wil' => 'nullable|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'n_provider' => 'required|string|max:255',
            'odp' => 'nullable|string|max:255',
            'sjalu_21' => 'nullable|string|max:255',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
            'id_user' => 'nullable|exists:users,id',
        ]);

        $data = $request->only([
            'fid',
            'provinsi',
            'kota',
            'kecamatan',
            'kelurahan',
            'k_ref_wil',
            'utilitas',
            'n_provider',
            'odp',
            'sjalu_21',
            'x',
            'y',
            'tgl_survey',
            'id_user',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        Provider::create($data);

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);

        $request->validate([
            'fid' => 'nullable|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'k_ref_wil' => 'nullable|string|max:255',
            'utilitas' => 'nullable|string|max:255',
            'n_provider' => 'required|string|max:255',
            'odp' => 'nullable|string|max:255',
            'sjalu_21' => 'nullable|string|max:255',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_survey' => 'nullable|date',
            'id_user' => 'nullable|exists:users,id',
        ]);

        $data = $request->only([
            'fid',
            'provinsi',
            'kota',
            'kecamatan',
            'kelurahan',
            'k_ref_wil',
            'utilitas',
            'n_provider',
            'odp',
            'sjalu_21',
            'x',
            'y',
            'tgl_survey',
            'id_user',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($provider->foto) {
                Storage::disk('public')->delete($provider->foto);
            }
            $data['foto'] = $request->file('foto')->store('provider_photos', 'public');
        }

        $provider->update($data);

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $provider = Provider::findOrFail($id);

        // Delete photo if exists
        if ($provider->foto) {
            Storage::disk('public')->delete($provider->foto);
        }

        $provider->delete();

        return redirect()->route('admin.provider.index')
            ->with('success', 'Data Provider berhasil dihapus');
    }

    /**
     * Export data to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Provider::with('user');

        // Apply same filters as index
        if ($request->filled('provinsi')) {
            $query->where('provinsi', $request->provinsi);
        }
        if ($request->filled('kota')) {
            $query->where('kota', $request->kota);
        }
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('n_provider')) {
            $query->where('n_provider', 'like', '%' . $request->n_provider . '%');
        }

        $providers = $query->orderBy('created_at', 'desc')->get();

        return response()->json($providers);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdnProvider;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CdnProviderController extends Controller
{
    public function index(): View
    {
        $providers = CdnProvider::orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.cdn-providers.index', compact('providers'));
    }

    public function create(): View
    {
        return view('admin.cdn-providers.form', [
            'provider' => new CdnProvider(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if (! empty($data['is_active'])) {
            CdnProvider::query()->update(['is_active' => false]);
        }

        CdnProvider::create($data);
        Setting::clearActiveCdnCache();

        return redirect()->route('admin.cdn-providers.index')
            ->with('success', 'CDN firmasi olusturuldu.');
    }

    public function edit(CdnProvider $cdnProvider): View
    {
        return view('admin.cdn-providers.form', [
            'provider' => $cdnProvider,
        ]);
    }

    public function update(Request $request, CdnProvider $cdnProvider): RedirectResponse
    {
        $data = $this->validatedData($request, $cdnProvider->id);

        if (! empty($data['is_active'])) {
            CdnProvider::whereKeyNot($cdnProvider->id)->update(['is_active' => false]);
        }

        if (! $request->filled('api_token')) {
            unset($data['api_token']);
        }

        $cdnProvider->update($data);
        Setting::clearActiveCdnCache();

        return redirect()->route('admin.cdn-providers.index')
            ->with('success', 'CDN firmasi guncellendi.');
    }

    public function destroy(CdnProvider $cdnProvider): RedirectResponse
    {
        $cdnProvider->delete();
        Setting::clearActiveCdnCache();

        return redirect()->route('admin.cdn-providers.index')
            ->with('success', 'CDN firmasi silindi.');
    }

    public function activate(CdnProvider $cdnProvider): RedirectResponse
    {
        CdnProvider::query()->update(['is_active' => false]);
        $cdnProvider->update(['is_active' => true]);
        Setting::clearActiveCdnCache();

        return redirect()->route('admin.cdn-providers.index')
            ->with('success', $cdnProvider->name.' aktif CDN olarak secildi.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:bunny,cloudflare,cloudfront,custom',
            'base_url' => 'nullable|url|max:255',
            'api_token' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
            'bunny_pull_zone' => 'nullable|string|max:255',
            'bunny_storage_zone' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $config = [
            'bunny_pull_zone' => $validated['bunny_pull_zone'] ?? null,
            'bunny_storage_zone' => $validated['bunny_storage_zone'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        unset($validated['bunny_pull_zone'], $validated['bunny_storage_zone'], $validated['notes']);

        $validated['config'] = $config;
        $validated['is_active'] = $request->boolean('is_active');
        $baseUrl = trim((string) ($validated['base_url'] ?? ''));
        if ($baseUrl === '' && $validated['provider'] === 'bunny') {
            // Bunny akisinda base URL domain bazli otomasyonla sonradan belirlenebilir.
            $baseUrl = 'https://b-cdn.net';
        }
        if ($baseUrl === '') {
            $baseUrl = 'https://cdn.local';
        }
        $validated['base_url'] = rtrim($baseUrl, '/');

        return $validated;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudflareAccount;
use Illuminate\Http\Request;

class CloudflareAccountController extends Controller
{
    private function normalizeApiToken(?string $token): string
    {
        $token = trim((string) $token);
        if (str_starts_with(strtolower($token), 'bearer ')) {
            $token = trim(substr($token, 7));
        }
        $token = preg_replace('/\s+/', '', $token) ?? $token;

        return $token;
    }

    public function index()
    {
        $accounts = CloudflareAccount::withCount('domains')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.cloudflare-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.cloudflare-accounts.form', ['account' => new CloudflareAccount]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'account_identifier' => 'nullable|string|max:64',
            'api_token' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        CloudflareAccount::create([
            'name' => $data['name'],
            'account_identifier' => $data['account_identifier'] ?? null,
            'api_token' => $this->normalizeApiToken($data['api_token']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.cloudflare-accounts.index')
            ->with('success', 'Cloudflare hesabı eklendi.');
    }

    public function edit(CloudflareAccount $cloudflareAccount)
    {
        return view('admin.cloudflare-accounts.form', ['account' => $cloudflareAccount]);
    }

    public function update(Request $request, CloudflareAccount $cloudflareAccount)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'account_identifier' => 'nullable|string|max:64',
            'api_token' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $cloudflareAccount->name = $data['name'];
        $cloudflareAccount->account_identifier = $data['account_identifier'] ?? null;
        $cloudflareAccount->is_active = $request->boolean('is_active');

        if ($request->filled('api_token')) {
            $cloudflareAccount->api_token = $this->normalizeApiToken($request->input('api_token'));
        }

        $cloudflareAccount->save();

        return redirect()->route('admin.cloudflare-accounts.index')
            ->with('success', 'Cloudflare hesabı güncellendi.');
    }

    public function destroy(CloudflareAccount $cloudflareAccount)
    {
        $cloudflareAccount->delete();

        return redirect()->route('admin.cloudflare-accounts.index')
            ->with('success', 'Cloudflare hesabı silindi. İlişkili domainler varsayılan tokena döndü.');
    }
}

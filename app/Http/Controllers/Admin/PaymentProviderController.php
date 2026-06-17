<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentProvider;
use Illuminate\Http\Request;

class PaymentProviderController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermission('settings.view'), 403);
        $providers = PaymentProvider::all();
        return view('admin.payment_providers.index', compact('providers'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        return view('admin.payment_providers.form', ['provider' => new PaymentProvider]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:paytr,iyzico,vakifbank,bank_direct',
            'config' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        PaymentProvider::create([
            'name' => $data['name'],
            'provider_type' => $data['provider_type'],
            'config' => $data['config'] ?? [],
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.payment-providers.index')->with('success', 'Ödeme sağlayıcı oluşturuldu.');
    }

    public function edit(PaymentProvider $paymentProvider)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        return view('admin.payment_providers.form', ['provider' => $paymentProvider]);
    }

    public function update(Request $request, PaymentProvider $paymentProvider)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:paytr,iyzico,vakifbank,bank_direct',
            'config' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $paymentProvider->update([
            'name' => $data['name'],
            'provider_type' => $data['provider_type'],
            'config' => $data['config'] ?? [],
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('admin.payment-providers.index')->with('success', 'Ödeme sağlayıcı güncellendi.');
    }

    public function destroy(PaymentProvider $paymentProvider)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        $paymentProvider->delete();
        return redirect()->route('admin.payment-providers.index')->with('success', 'Ödeme sağlayıcı silindi.');
    }
}

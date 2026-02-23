<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use App\Models\ServerPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $servers = ApiServer::with('pricing')->get();
        return view('admin.pricing.index', ['servers' => $servers]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:api_servers,id',
            'country_code' => 'nullable|string|max:10',
            'service_code' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);
        ServerPricing::updateOrCreate(
            [
                'server_id' => $validated['server_id'],
                'country_code' => $validated['country_code'] ?: null,
                'service_code' => $validated['service_code'],
            ],
            ['price' => $validated['price'], 'active' => true]
        );
        return back()->with('success', 'Pricing saved.');
    }

    public function destroy(ServerPricing $pricing): RedirectResponse
    {
        $pricing->delete();
        return back()->with('success', 'Pricing removed.');
    }
}

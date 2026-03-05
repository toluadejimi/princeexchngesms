<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.index', [
            'usd_to_ngn_rate' => SiteSetting::usdToNgnRate(),
            'naira_margin_amount' => SiteSetting::nairaMarginAmount(),
            'naira_margin_percent' => SiteSetting::nairaMarginPercent(),
            'display_currency' => SiteSetting::displayCurrency(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'usd_to_ngn_rate' => 'required|numeric|min:0',
            'naira_margin_amount' => 'required|numeric|min:0',
            'naira_margin_percent' => 'nullable|numeric|min:0',
            'display_currency' => 'required|in:USD,NGN',
        ]);
        SiteSetting::set('usd_to_ngn_rate', $validated['usd_to_ngn_rate']);
        SiteSetting::set('naira_margin_amount', $validated['naira_margin_amount']);
        SiteSetting::set('naira_margin_percent', $validated['naira_margin_percent'] ?? 0);
        SiteSetting::set('display_currency', $validated['display_currency']);
        return back()->with('success', 'Pricing settings saved. Applied to both Server 1 and Server 2.');
    }
}

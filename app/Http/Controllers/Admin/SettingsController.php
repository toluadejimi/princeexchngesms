<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use App\Models\SiteSetting;
use App\Services\Sms\SmsServerFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $daisyBalance = null;
        $daisyError = null;
        $smspoolBalance = null;
        $smspoolError = null;

        $usaServer = ApiServer::active()->where('type', 'usa_only')->first();
        if ($usaServer) {
            try {
                $client = SmsServerFactory::make($usaServer);
                $daisyBalance = $client->getBalance();
            } catch (\Throwable $e) {
                $daisyError = $e->getMessage();
            }
        } else {
            $daisyError = 'USA server not configured.';
        }

        $multiServer = ApiServer::active()->where('type', 'multi_country')->first();
        if ($multiServer) {
            try {
                $client = SmsServerFactory::make($multiServer);
                $smspoolBalance = $client->getBalance();
            } catch (\Throwable $e) {
                $smspoolError = $e->getMessage();
            }
        } else {
            $smspoolError = 'Other Countries server not configured.';
        }

        return view('admin.settings.index', [
            'site_name' => SiteSetting::get('site_name', config('app.name', '')),
            'site_logo_url' => SiteSetting::logoUrl(),
            'site_favicon_url' => SiteSetting::faviconUrl(),
            'display_currency' => SiteSetting::displayCurrency(),
            'usd_to_ngn_rate' => SiteSetting::usdToNgnRate(),
            'naira_margin_percent' => SiteSetting::nairaMarginPercent(),
            'naira_margin_amount' => SiteSetting::nairaMarginAmount(),
            'manual_bank_name' => SiteSetting::get('manual_bank_name', ''),
            'manual_account_no' => SiteSetting::get('manual_account_no', ''),
            'manual_account_name' => SiteSetting::get('manual_account_name', ''),
            'manual_funding_enabled' => SiteSetting::get('manual_funding_enabled', '0'),
            'telegram_url' => SiteSetting::telegramUrl(),
            'daisy_balance' => $daisyBalance,
            'daisy_error' => $daisyError,
            'smspool_balance' => $smspoolBalance,
            'smspool_error' => $smspoolError,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,gif,jpeg,jpg,webp|max:512',
            'display_currency' => 'required|in:USD,NGN',
            'usd_to_ngn_rate' => 'nullable|numeric|min:0',
            'naira_margin_percent' => 'nullable|numeric|min:0',
            'naira_margin_amount' => 'nullable|numeric|min:0',
            'manual_bank_name' => 'nullable|string|max:255',
            'manual_account_no' => 'nullable|string|max:64',
            'manual_account_name' => 'nullable|string|max:255',
            'manual_funding_enabled' => 'nullable|in:0,1',
            'telegram_url' => 'nullable|string|max:500',
        ]);

        SiteSetting::set('site_name', $validated['site_name'] ?? config('app.name', ''));

        if ($request->hasFile('logo')) {
            $oldPath = SiteSetting::logoPath();
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('site', 'public');
            SiteSetting::set('site_logo', $path);
        }

        if ($request->hasFile('favicon')) {
            $oldPath = SiteSetting::faviconPath();
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('favicon')->store('site', 'public');
            SiteSetting::set('site_favicon', $path);
        }

        SiteSetting::set('display_currency', $validated['display_currency']);
        SiteSetting::set('usd_to_ngn_rate', $validated['usd_to_ngn_rate'] ?? 0);
        SiteSetting::set('naira_margin_percent', $validated['naira_margin_percent'] ?? 0);
        SiteSetting::set('naira_margin_amount', $validated['naira_margin_amount'] ?? 0);
        SiteSetting::set('manual_bank_name', $validated['manual_bank_name'] ?? '');
        SiteSetting::set('manual_account_no', $validated['manual_account_no'] ?? '');
        SiteSetting::set('manual_account_name', $validated['manual_account_name'] ?? '');
        SiteSetting::set('manual_funding_enabled', $validated['manual_funding_enabled'] ?? '0');
        SiteSetting::set('telegram_url', $validated['telegram_url'] ?? '');

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use App\Models\SiteSetting;
use App\Services\Sms\SmsServerFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $server1 = $this->providerBalance('getatext', 'Server 1');
        $server2 = $this->providerBalance('multi_country', 'Server 2');
        $server3 = $this->providerBalance('fivesim', 'Server 3');

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
            'server1_balance' => $server1['balance'],
            'server1_error' => $server1['error'],
            'server1_cached' => $server1['cached'],
            'server2_balance' => $server2['balance'],
            'server2_error' => $server2['error'],
            'server2_cached' => $server2['cached'],
            'server3_balance' => $server3['balance'],
            'server3_error' => $server3['error'],
            'server3_cached' => $server3['cached'],
            'login_popup_enabled' => SiteSetting::get('login_popup_enabled', '0'),
            'login_popup_title' => SiteSetting::get('login_popup_title', ''),
            'login_popup_message' => SiteSetting::get('login_popup_message', ''),
        ]);
    }

    /**
     * @return array{balance: float|null, error: string|null, cached: bool}
     */
    private function providerBalance(string $type, string $label): array
    {
        $server = ApiServer::activeCached()->firstWhere('type', $type);
        if (! $server) {
            return ['balance' => null, 'error' => "{$label} not configured or disabled.", 'cached' => false];
        }

        $cacheKey = "provider_balance_{$server->id}";
        $cachedBalance = Cache::get($cacheKey);

        try {
            $balance = SmsServerFactory::make($server)->getBalance();
            Cache::put($cacheKey, $balance, now()->addMinutes(5));

            return ['balance' => $balance, 'error' => null, 'cached' => false];
        } catch (\Throwable $e) {
            if ($cachedBalance !== null) {
                return ['balance' => (float) $cachedBalance, 'error' => $this->providerBalanceError($e), 'cached' => true];
            }

            return ['balance' => null, 'error' => $this->providerBalanceError($e), 'cached' => false];
        }
    }

    private function providerBalanceError(\Throwable $e): string
    {
        $message = $e->getMessage();
        if (str_contains($message, 'cURL error 7') || str_contains($message, 'Failed to connect')) {
            return 'Unable to connect to provider. Check the server network, API base URL, or firewall.';
        }
        if (str_contains($message, 'cURL error 28') || str_contains($message, 'timed out')) {
            return 'Provider request timed out. Please try again shortly.';
        }

        return 'Unable to load provider balance right now.';
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
            'login_popup_enabled' => 'nullable|in:0,1',
            'login_popup_title' => 'nullable|string|max:255',
            'login_popup_message' => 'nullable|string|max:2000',
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
        SiteSetting::set('login_popup_enabled', $validated['login_popup_enabled'] ?? '0');
        SiteSetting::set('login_popup_title', $validated['login_popup_title'] ?? '');
        SiteSetting::set('login_popup_message', $validated['login_popup_message'] ?? '');

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ApiServer;
use App\Models\SiteSetting;
use App\Services\PricingService;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function __construct(
        protected RentalService $rentalService,
        protected PricingService $pricingService
    ) {}

    public function create(): View
    {
        $servers = ApiServer::active()->orderBy('sort_order')->get();
        return view('rentals.create', ['servers' => $servers]);
    }

    /** USA Server only (DaisySMS) – no country selector. */
    public function createUsa(): View|\Illuminate\Http\RedirectResponse
    {
        $server = ApiServer::active()->where('type', 'usa_only')->first();
        if (!$server) {
            return redirect()->route('dashboard')->with('error', 'USA Server is not available at the moment.');
        }
        return view('rentals.create-single', array_merge($this->priceSettings(), [
            'server' => $server,
            'showCountry' => false,
            'title' => 'USA Server',
            'subtitle' => 'Rent a US number for WhatsApp, Telegram, Google, and more.',
        ]));
    }

    /** Other Countries server (SMSPool) – with country selector. */
    public function createCountries(): View|\Illuminate\Http\RedirectResponse
    {
        $server = ApiServer::active()->where('type', 'multi_country')->first();
        if (!$server) {
            return redirect()->route('dashboard')->with('error', 'Other Countries server is not available at the moment.');
        }
        return view('rentals.create-single', array_merge($this->priceSettings(), [
            'server' => $server,
            'showCountry' => true,
            'title' => 'Other Countries',
            'subtitle' => 'Rent a number from 150+ countries.',
        ]));
    }

    /** Price display settings (safe defaults if SiteSetting fails). */
    private function priceSettings(): array
    {
        try {
            return [
                'priceDisplay' => SiteSetting::displayCurrency(),
                'usdToNgnRate' => SiteSetting::usdToNgnRate(),
                'nairaMarginPercent' => SiteSetting::nairaMarginPercent(),
                'nairaMarginAmount' => SiteSetting::nairaMarginAmount(),
            ];
        } catch (\Throwable) {
            return [
                'priceDisplay' => 'USD',
                'usdToNgnRate' => 0.0,
                'nairaMarginPercent' => 0.0,
                'nairaMarginAmount' => 0.0,
            ];
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:api_servers,id',
            'service_code' => 'required|string|max:50',
            'country_code' => 'nullable|string|max:10',
            'country_id' => 'nullable|string|max:20',
            'areas' => 'nullable|string|max:200',
            'carriers' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:20',
            'pool_id' => 'nullable|string|max:20',
        ]);

        $server = ApiServer::active()->findOrFail($validated['server_id']);
        $countryCode = $server->isUsaOnly() ? 'US' : ($validated['country_code'] ?? '');
        if (!$countryCode) {
            return response()->json(['message' => 'Please select a country.'], 422);
        }

        $options = [];
        if (!empty($validated['areas'])) {
            $options['areas'] = $validated['areas'];
        }
        if (!empty($validated['carriers'])) {
            $options['carriers'] = $validated['carriers'];
        }
        if (!empty($validated['number'])) {
            $options['number'] = $validated['number'];
        }
        if (!empty($validated['pool_id']) && $server->isMultiCountry()) {
            $options['pool_id'] = $validated['pool_id'];
        }
        if (!empty($validated['country_id']) && $server->isMultiCountry()) {
            $options['country_id'] = $validated['country_id'];
        }

        try {
            $rental = $this->rentalService->createRental(
                $request->user()->id,
                (int) $validated['server_id'],
                $validated['service_code'],
                $countryCode,
                $options
            );
            return response()->json([
                'message' => 'Number rented successfully.',
                'rental' => [
                    'id' => $rental->id,
                    'phone_number' => $rental->phone_number,
                    'order_id' => $rental->order_id,
                    'cost' => $rental->cost,
                    'expires_at' => $rental->expires_at?->toIso8601String(),
                ],
                'redirect' => route('dashboard'),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Rental store failed', [
                'message' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $message = 'Unable to complete your request. Please try again in a moment.';
            if ($e instanceof \RuntimeException) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'Insufficient wallet balance')
                    || str_contains($msg, 'No numbers available')
                    || str_contains($msg, 'Price exceeded')
                    || str_contains($msg, 'Pricing not configured')
                    || str_contains($msg, 'Insufficient balance on provider')
                    || str_contains($msg, 'Too many active rentals')
                    || str_contains($msg, 'DaisySMS order failed')
                    || str_contains($msg, 'Order failed')
                    || str_contains($msg, 'SMSPool')
                    || str_contains($msg, 'API failed')) {
                    $message = $msg;
                }
            }
            return response()->json(['message' => $message], 422);
        }
    }

    public function cancel(int $id, Request $request): RedirectResponse|JsonResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        try {
            $this->rentalService->cancelRental($rental);
            return redirect()->route('dashboard')->with('message', 'Rental cancelled. Amount refunded.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Rental cancel failed', ['message' => $e->getMessage(), 'rental_id' => $id]);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to cancel. Please try again.'], 422);
            }
            return redirect()->route('dashboard')->with('error', 'Unable to cancel. Please try again.');
        }
    }

    public function expireIfOverdue(int $id, Request $request): JsonResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        if ($rental->isActive() && $rental->expires_at && $rental->expires_at->isPast()) {
            $this->rentalService->expireRental($rental);
            $rental->refresh();
        }
        return response()->json(['status' => $rental->status, 'expired' => $rental->status === \App\Models\Rental::STATUS_EXPIRED]);
    }

    public function status(int $id, Request $request): JsonResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        $this->rentalService->checkAndUpdateSms($rental);
        $rental->refresh();
        return response()->json([
            'status' => $rental->status,
            'sms_code' => $rental->sms_code,
            'sms_messages' => $rental->getSmsMessagesList(),
            'expires_at' => $rental->expires_at?->toIso8601String(),
        ]);
    }

    /** Other Countries (SMSPool) only: resend SMS for this order */
    public function resend(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        if (!$rental->server || !$rental->server->isMultiCountry()) {
            $msg = 'Resend is only available for Other Countries rentals.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
        try {
            $client = \App\Services\Sms\SmsServerFactory::make($rental->server);
            $client->resendSms($rental->order_id);
            $this->rentalService->checkAndUpdateSms($rental);
            $rental->refresh();
            $msg = 'Resend requested.';
            return $request->expectsJson()
                ? response()->json(['message' => $msg, 'status' => $rental->status, 'sms_messages' => $rental->getSmsMessagesList()])
                : redirect()->route('dashboard')->with('message', $msg);
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'Resend failed.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
    }

    /** Other Countries (SMSPool) only: activate SMS for this order */
    public function activate(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        if (!$rental->server || !$rental->server->isMultiCountry()) {
            $msg = 'Activate is only available for Other Countries rentals.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
        try {
            $client = \App\Services\Sms\SmsServerFactory::make($rental->server);
            $client->activateSms($rental->order_id);
            $this->rentalService->checkAndUpdateSms($rental);
            $rental->refresh();
            $msg = 'Activate requested.';
            return $request->expectsJson()
                ? response()->json(['message' => $msg, 'status' => $rental->status])
                : redirect()->route('dashboard')->with('message', $msg);
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'Activate failed.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
    }

    /** Other Countries (SMSPool) only: reactivate SMS for this order */
    public function reactivate(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $rental = \App\Models\Rental::where('user_id', $request->user()->id)->findOrFail($id);
        if (!$rental->server || !$rental->server->isMultiCountry()) {
            $msg = 'Reactivate is only available for Other Countries rentals.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
        try {
            $client = \App\Services\Sms\SmsServerFactory::make($rental->server);
            $client->reactivateSms($rental->order_id);
            $this->rentalService->checkAndUpdateSms($rental);
            $rental->refresh();
            $msg = 'Reactivate requested.';
            return $request->expectsJson()
                ? response()->json(['message' => $msg, 'status' => $rental->status])
                : redirect()->route('dashboard')->with('message', $msg);
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'Reactivate failed.';
            return $request->expectsJson() ? response()->json(['message' => $msg], 422) : redirect()->route('dashboard')->with('error', $msg);
        }
    }

    public function services(Request $request): JsonResponse
    {
        $serverId = (int) $request->query('server_id');
        $countryCode = $request->query('country_code');
        $server = ApiServer::active()->findOrFail($serverId);
        if ($server->isUsaOnly()) {
            $countryCode = 'US';
        }
        $services = $this->pricingService->getServicesWithPrices($serverId, $countryCode);
        return response()->json(['services' => $services]);
    }

    public function countries(Request $request): JsonResponse
    {
        $serverId = (int) $request->query('server_id');
        \Illuminate\Support\Facades\Log::info('Other Countries: countries requested', [
            'server_id' => $serverId,
            'user_id' => $request->user()?->id,
        ]);

        $server = ApiServer::active()->findOrFail($serverId);
        $countries = [];
        $failureReason = null;
        try {
            $client = \App\Services\Sms\SmsServerFactory::make($server);
            $countries = $client->getCountries();
            if (empty($countries)) {
                $failureReason = 'API returned empty list';
            }
        } catch (\Throwable $e) {
            $failureReason = 'Exception: ' . $e->getMessage();
            \Illuminate\Support\Facades\Log::warning('Other Countries: countries fetch failed', [
                'server_id' => $serverId,
                'server_name' => $server->name,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        if (empty($countries) && $server->isMultiCountry()) {
            \Illuminate\Support\Facades\Log::info('Other Countries: using fallback country list', [
                'server_id' => $serverId,
                'reason' => $failureReason ?? 'empty',
                'fallback_count' => count(self::fallbackCountries()),
            ]);
            $countries = self::fallbackCountries();
        }

        \Illuminate\Support\Facades\Log::info('Other Countries: returning countries', [
            'server_id' => $serverId,
            'count' => count($countries),
        ]);
        return response()->json(['countries' => $countries]);
    }

    /** Live price for selected country/service/pool (SMSPool /request/price). Returns price in NGN (with conversion + margin) and success_rate. */
    public function price(Request $request): JsonResponse
    {
        $request->validate([
            'server_id' => 'required|exists:api_servers,id',
            'country_id' => 'required|integer|min:1',
            'service_code' => 'required|string|max:50',
            'pool_id' => 'nullable|string|max:20',
        ]);
        $server = ApiServer::active()->findOrFail((int) $request->query('server_id'));
        if (!$server->isMultiCountry()) {
            return response()->json(['message' => 'Price only available for Other Countries server.'], 422);
        }
        $countryId = (int) $request->query('country_id');
        $serviceId = (int) $request->query('service_code');
        $poolId = $request->query('pool_id');
        $poolIdInt = $poolId !== null && $poolId !== '' ? (int) $poolId : null;

        try {
            $client = \App\Services\Sms\SmsServerFactory::make($server);
            $result = $client->getPrice($countryId, $serviceId, $poolIdInt);
        } catch (\Throwable $e) {
            return response()->json([
                'price_usd' => 0,
                'price_ngn' => 0,
                'success_rate' => 0,
                'currency' => SiteSetting::displayCurrency(),
                'message' => $e->getMessage(),
            ], 200);
        }

        $priceUsd = (float) ($result['price'] ?? 0);
        $successRate = (int) ($result['success_rate'] ?? 0);
        $priceNgn = SiteSetting::displayCurrency() === 'NGN' && $priceUsd > 0
            ? (int) round(SiteSetting::usdToNairaTotal($priceUsd))
            : 0;

        return response()->json([
            'price_usd' => round($priceUsd, 4),
            'price_ngn' => $priceNgn,
            'success_rate' => $successRate,
            'currency' => SiteSetting::displayCurrency(),
        ]);
    }

    /** Pools for multi-country (SMSPool) server. */
    public function pools(Request $request): JsonResponse
    {
        $serverId = (int) $request->query('server_id');
        $server = ApiServer::active()->findOrFail($serverId);
        if (!$server->isMultiCountry()) {
            return response()->json(['pools' => []]);
        }
        try {
            $client = \App\Services\Sms\SmsServerFactory::make($server);
            $pools = method_exists($client, 'getPools') ? $client->getPools() : [];
            return response()->json(['pools' => $pools]);
        } catch (\Throwable $e) {
            return response()->json(['pools' => []]);
        }
    }

    /** Fallback country list when multi-country API returns empty or fails. */
    private static function fallbackCountries(): array
    {
        $list = [
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'NG', 'name' => 'Nigeria'],
            ['code' => 'IN', 'name' => 'India'],
            ['code' => 'PK', 'name' => 'Pakistan'],
            ['code' => 'BD', 'name' => 'Bangladesh'],
            ['code' => 'KE', 'name' => 'Kenya'],
            ['code' => 'GH', 'name' => 'Ghana'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'RU', 'name' => 'Russia'],
            ['code' => 'UA', 'name' => 'Ukraine'],
            ['code' => 'PL', 'name' => 'Poland'],
            ['code' => 'BR', 'name' => 'Brazil'],
            ['code' => 'MX', 'name' => 'Mexico'],
            ['code' => 'ID', 'name' => 'Indonesia'],
            ['code' => 'PH', 'name' => 'Philippines'],
            ['code' => 'VN', 'name' => 'Vietnam'],
        ];
        return array_map(fn ($c) => ['code' => $c['code'], 'name' => $c['name'], 'provider_id' => $c['code']], $list);
    }
}

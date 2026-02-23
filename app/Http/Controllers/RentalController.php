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
            'areas' => 'nullable|string|max:200',
            'carriers' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:20',
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
                    || str_contains($msg, 'DaisySMS order failed')) {
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
        $server = ApiServer::active()->findOrFail($serverId);
        $client = \App\Services\Sms\SmsServerFactory::make($server);
        $countries = $client->getCountries();
        return response()->json(['countries' => $countries]);
    }
}

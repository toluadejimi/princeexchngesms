<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\VtuTransaction;
use App\Services\SprintPayVtuService;
use App\Services\VtuPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VtuController extends Controller
{
    private const CATALOG_PATHS = [
        'services' => 'get-service',
        'data' => 'get-data-variations',
        'electricity' => 'get-electricity-variations',
        'cable' => 'cable-plan',
    ];

    public function index(Request $request, SprintPayVtuService $sprintPay): View
    {
        $transactions = VtuTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('vtu.index', [
            'enabled' => $sprintPay->enabled(),
            'configured' => $sprintPay->configured(),
            'transactions' => $transactions,
            'walletBalance' => (float) $request->user()->wallet_balance,
            'currency' => SiteSetting::displayCurrency(),
        ]);
    }

    public function catalog(string $catalog, Request $request, SprintPayVtuService $sprintPay): JsonResponse
    {
        if (! isset(self::CATALOG_PATHS[$catalog])) {
            return response()->json(['message' => 'Unknown catalog.'], 404);
        }

        try {
            $data = $sprintPay->catalog(self::CATALOG_PATHS[$catalog], $request->query());

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function purchase(Request $request, VtuPurchaseService $purchaseService): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:airtime,data,cable,electricity',
            'service_id' => 'required|string|max:100',
            'amount' => 'required|numeric|min:50|max:500000',
            'phone' => 'nullable|string|max:30',
            'variation_code' => 'nullable|string|max:120',
            'billers_code' => 'nullable|string|max:120',
            'meter_type' => 'nullable|string|max:30',
            'customer_name' => 'nullable|string|max:255',
        ]);

        $type = $validated['type'];
        $amount = (float) $validated['amount'];

        $payload = [
            'service_id' => $validated['service_id'],
            'amount' => $amount,
        ];

        if (in_array($type, ['airtime', 'data'], true)) {
            $request->validate(['phone' => 'required|string|max:30']);
            $payload['phone'] = $validated['phone'];
        }

        if ($type === 'data') {
            $request->validate(['variation_code' => 'required|string|max:120']);
            $payload['variation_code'] = $validated['variation_code'];
        }

        if ($type === 'cable') {
            $request->validate([
                'variation_code' => 'required|string|max:120',
                'billers_code' => 'required|string|max:120',
            ]);
            $payload['variation_code'] = $validated['variation_code'];
            $payload['billersCode'] = $validated['billers_code'];
            $payload['phone'] = $validated['phone'] ?? $request->user()->email;
        }

        if ($type === 'electricity') {
            $request->validate([
                'billers_code' => 'required|string|max:120',
                'meter_type' => 'required|string|max:30',
                'phone' => 'required|string|max:30',
            ]);
            $payload['billersCode'] = $validated['billers_code'];
            $payload['variation_code'] = $validated['meter_type'];
            $payload['phone'] = $validated['phone'];
        }

        try {
            $transaction = $purchaseService->purchase($request->user(), $type, $amount, $payload);

            return redirect()
                ->route('vtu.index')
                ->with('message', 'VTU purchase successful. Ref: '.$transaction->reference);
        } catch (\Throwable $e) {
            return redirect()
                ->route('vtu.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}

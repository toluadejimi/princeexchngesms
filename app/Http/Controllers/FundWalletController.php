<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\SiteSetting;
use App\Models\VirtualAccount;
use App\Services\SprintPayService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FundWalletController extends Controller
{
    public function __construct(
        protected WalletService $wallet,
        protected SprintPayService $sprintPay
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $account = VirtualAccount::where('user_id', $user->id)->first();
        $transactions = $user->walletTransactions()->latest()->paginate(15);

        $manualBankName = \App\Models\SiteSetting::get('manual_bank_name', '');
        $manualAccountNo = \App\Models\SiteSetting::get('manual_account_no', '');
        $manualAccountName = \App\Models\SiteSetting::get('manual_account_name', '');
        $manualFundingEnabled = (bool) \App\Models\SiteSetting::get('manual_funding_enabled', '0');

        return view('fund-wallet.index', [
            'user' => $user,
            'account' => $account,
            'account_no' => $account?->account_no,
            'account_name' => $account?->account_name,
            'bank_name' => $account?->bank_name,
            'transactions' => $transactions,
            'manualBankName' => $manualBankName,
            'manualAccountNo' => $manualAccountNo,
            'manualAccountName' => $manualAccountName,
            'manualFundingEnabled' => $manualFundingEnabled,
        ]);
    }

    public function generateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (VirtualAccount::where('user_id', $user->id)->exists()) {
            return redirect()->route('fund-wallet.index')->with('message', 'You already have a virtual account.');
        }

        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $key = config('services.sprintpay.key');
        if (!$key) {
            return back()->with('error', 'Virtual account service is not configured.');
        }

        $result = $this->sprintPay->generateVirtualAccount(
            $user->email,
            $request->input('fullname', $user->name ?? $user->email),
            $key
        );

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Failed to generate account.');
        }

        VirtualAccount::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'account_no' => $result['account_number'],
            'account_name' => $result['account_name'],
            'bank_name' => $result['bank_name'],
        ]);

        return redirect()->route('fund-wallet.index')->with('message', 'Virtual account created successfully.');
    }

    public function fundNow(Request $request): RedirectResponse|View
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:1,2', // 1 = instant, 2 = manual
        ]);

        $user = $request->user();
        $amount = (float) $request->amount;
        $typeInput = $request->type;
        $isInstant = $typeInput == '1';

        if ($isInstant) {
            if ($amount < 1000) {
                return back()->with('error', 'Minimum instant funding is ₦1,000.');
            }
            if ($amount > 100_000) {
                return back()->with('error', 'Maximum instant funding is ₦100,000.');
            }
        } else {
            if ($amount < 100) {
                return back()->with('error', 'Minimum amount is ₦100.');
            }
            if ($amount > 100_000) {
                return back()->with('error', 'Maximum amount is ₦100,000.');
            }
        }

        FundRequest::where('user_id', $user->id)->where('status', FundRequest::STATUS_PENDING)->delete();

        $ref = 'VERF' . random_int(100, 999) . date('ymdhis');

        $fundRequest = FundRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'ref_id' => $ref,
            'type' => $isInstant ? FundRequest::TYPE_INSTANT : FundRequest::TYPE_MANUAL,
            'status' => FundRequest::STATUS_PENDING,
        ]);

        if ($isInstant) {
            $key = config('services.sprintpay.key');
            if (!$key) {
                $fundRequest->update(['status' => FundRequest::STATUS_FAILED]);
                return back()->with('error', 'Payment service is not configured.');
            }
            $url = $this->sprintPay->paymentUrl($amount, $ref, $user->email);
            return redirect()->away($url);
        }

        $manualAccountName = SiteSetting::get('manual_account_name', '');
        $manualAccountNo = SiteSetting::get('manual_account_no', '');
        $manualBankName = SiteSetting::get('manual_bank_name', '');

        return view('fund-wallet.manual', [
            'fundRequest' => $fundRequest,
            'amount' => $amount,
            'manualAccountName' => $manualAccountName,
            'manualAccountNo' => $manualAccountNo,
            'manualBankName' => $manualBankName,
        ]);
    }

    public function fundManualSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'receipt' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $user = $request->user();
        $amount = (float) $request->amount;

        $fundRequest = FundRequest::where('user_id', $user->id)
            ->where('amount', $amount)
            ->where('type', FundRequest::TYPE_MANUAL)
            ->where('status', FundRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$fundRequest) {
            return back()->with('error', 'Pending payment not found. Please start again.');
        }

        $path = $request->file('receipt')->store('receipts', 'public');
        $fundRequest->update(['receipt_path' => $path]);

        return redirect()->route('fund-wallet.index')->with('message', 'Receipt submitted. Your wallet will be credited after verification.');
    }

    /**
     * Webhook called by SprintPay on successful payment.
     * Payload: amount, email, order_id, session_id, account_no
     */
    public function webhook(Request $request): Response
    {
        $payload = $request->all();
        \Illuminate\Support\Facades\Log::info('SprintPay webhook', $payload);

        $amount = (float) ($payload['amount'] ?? 0);
        $email = $payload['email'] ?? '';
        $orderId = $payload['order_id'] ?? '';

        if ($amount <= 0 || !$email) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $fundRequest = FundRequest::where('user_id', $user->id)
            ->where('status', FundRequest::STATUS_PENDING)
            ->where(function ($q) use ($amount, $orderId) {
                $q->where('ref_id', $orderId)->orWhere('order_id', $orderId);
                if ($amount > 0) {
                    $q->orWhere('amount', $amount);
                }
            })
            ->latest()
            ->first();

        if (!$fundRequest) {
            $fundRequest = FundRequest::where('user_id', $user->id)
                ->where('status', FundRequest::STATUS_PENDING)
                ->where('amount', $amount)
                ->latest()
                ->first();
        }

        if ($fundRequest && $fundRequest->status === FundRequest::STATUS_COMPLETED) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        try {
            $this->wallet->adjust(
                $user,
                $amount,
                \App\Models\WalletTransaction::TYPE_DEPOSIT,
                [
                    'fund_request_id' => $fundRequest?->id,
                    'order_id' => $orderId,
                    'session_id' => $payload['session_id'] ?? null,
                    'account_no' => $payload['account_no'] ?? null,
                    'source' => 'sprintpay',
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SprintPay webhook credit failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Credit failed'], 500);
        }

        if ($fundRequest) {
            $fundRequest->update([
                'status' => FundRequest::STATUS_COMPLETED,
                'order_id' => $orderId,
                'session_id' => $payload['session_id'] ?? null,
                'account_no' => $payload['account_no'] ?? null,
            ]);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}

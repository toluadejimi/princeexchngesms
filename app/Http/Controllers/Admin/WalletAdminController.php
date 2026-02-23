<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WalletAdminController extends Controller
{
    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
        ]);
        $user = User::findOrFail($validated['user_id']);
        $wallet = app(WalletService::class);
        $wallet->adjust($user, (float) $validated['amount'], 'admin_adjustment', ['note' => $validated['note'] ?? '']);
        return back()->with('success', 'Wallet updated.');
    }
}

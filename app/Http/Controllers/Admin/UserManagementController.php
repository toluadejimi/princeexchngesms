<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('email', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $user->loadCount(['rentals', 'fundRequests']);
        $manualReceipts = FundRequest::where('user_id', $user->id)
            ->where('type', FundRequest::TYPE_MANUAL)
            ->whereNotNull('receipt_path')
            ->orderBy('created_at', 'desc')
            ->get();
        $rentals = $user->rentals()->with('server:id,name,type')->latest()->paginate(15);

        return view('admin.users.show', [
            'user' => $user,
            'manualReceipts' => $manualReceipts,
            'rentals' => $rentals,
        ]);
    }

    public function block(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Cannot block yourself.');
        }
        if ($user->is_admin) {
            return back()->with('error', 'Cannot block an admin.');
        }
        $user->update(['is_blocked' => true]);
        return back()->with('success', 'User blocked.');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update(['is_blocked' => false]);
        return back()->with('success', 'User unblocked.');
    }

    public function fund(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
        ]);
        $user = User::findOrFail($validated['user_id']);
        app(WalletService::class)->adjust(
            $user,
            (float) $validated['amount'],
            \App\Models\WalletTransaction::TYPE_ADMIN_ADJUSTMENT,
            ['note' => $validated['note'] ?? '']
        );
        return back()->with('success', 'Wallet funded.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }
        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete an admin.');
        }
        $user->fundRequests()->delete();
        $user->walletTransactions()->delete();
        $user->virtualAccount()->delete();
        foreach ($user->rentals as $r) {
            $r->delete();
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function receipt(User $user, FundRequest $fundRequest): StreamedResponse|RedirectResponse
    {
        if ($fundRequest->user_id !== $user->id || !$fundRequest->receipt_path) {
            abort(404);
        }
        if (!Storage::disk('public')->exists($fundRequest->receipt_path)) {
            return back()->with('error', 'File not found.');
        }
        $mime = Storage::disk('public')->mimeType($fundRequest->receipt_path);
        $name = basename($fundRequest->receipt_path);
        return Storage::disk('public')->response($fundRequest->receipt_path, $name, [
            'Content-Type' => $mime,
        ]);
    }

    public function loginAs(User $user): RedirectResponse
    {
        if ($user->is_blocked) {
            return back()->with('error', 'Cannot log in as a blocked user.');
        }
        Auth::login($user, true);
        return redirect()->route('dashboard')->with('success', __('Logged in as :name.', ['name' => $user->name]));
    }
}

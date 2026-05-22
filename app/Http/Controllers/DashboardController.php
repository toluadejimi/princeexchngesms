<?php

namespace App\Http\Controllers;

use App\Models\ApiServer;
use App\Models\Notification as AppNotification;
use App\Models\Rental;
use App\Models\SiteSetting;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $showLoginPopup = $request->session()->get('show_login_popup', false) && SiteSetting::loginPopupEnabled()
            && (SiteSetting::loginPopupTitle() !== '' || SiteSetting::loginPopupMessage() !== '');

        return view('dashboard.index', [
            'user' => $request->user(),
            'lazyDashboard' => true,
            'showLoginPopup' => $showLoginPopup,
            'loginPopupTitle' => SiteSetting::loginPopupTitle(),
            'loginPopupMessage' => SiteSetting::loginPopupMessage(),
        ]);
    }

    public function data(Request $request, RentalService $rentalService): JsonResponse
    {
        $user = $request->user();
        $rentalService->expireOverdueRentalsForUser($user->id);

        $query = Rental::where('user_id', $user->id)->with('server')->latest();
        if ($request->filled('server')) {
            $query->where('server_id', $request->query('server'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $rentals = $query->paginate(15)
            ->withPath(route('dashboard'))
            ->withQueryString();

        $html = view('dashboard.partials.lazy-content', [
            'user' => $user,
            'rentals' => $rentals,
            'activeCount' => Rental::where('user_id', $user->id)->active()->count(),
            'servers' => ApiServer::active()->orderBy('sort_order')->get(),
            'unreadNotificationCount' => AppNotification::whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('read_at'))->count(),
        ])->render();

        return response()->json(['html' => $html])
            ->header('Cache-Control', 'private, max-age=15, stale-while-revalidate=30');
    }

    /** Dismiss the login popup for this session (user clicked Disable/Close). */
    public function dismissLoginPopup(Request $request): JsonResponse
    {
        $request->session()->forget('show_login_popup');
        return response()->json(['ok' => true]);
    }
}

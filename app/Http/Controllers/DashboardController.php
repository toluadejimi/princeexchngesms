<?php

namespace App\Http\Controllers;

use App\Models\ApiServer;
use App\Models\Notification as AppNotification;
use App\Models\Rental;
use App\Models\SiteSetting;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, RentalService $rentalService): View
    {
        $showLoginPopup = $request->session()->get('show_login_popup', false) && SiteSetting::loginPopupEnabled()
            && (SiteSetting::loginPopupTitle() !== '' || SiteSetting::loginPopupMessage() !== '');

        return view('dashboard.index', array_merge($this->dashboardPayload($request, $rentalService), [
            'user' => $request->user(),
            'lazyDashboard' => false,
            'showLoginPopup' => $showLoginPopup,
            'loginPopupTitle' => SiteSetting::loginPopupTitle(),
            'loginPopupMessage' => SiteSetting::loginPopupMessage(),
        ]));
    }

    public function data(Request $request, RentalService $rentalService): JsonResponse
    {
        $payload = $this->dashboardPayload($request, $rentalService);

        $html = view('dashboard.partials.lazy-content', $payload)->render();

        return response()->json(['html' => $html])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * @return array{
     *     user: \App\Models\User,
     *     rentals: LengthAwarePaginator,
     *     activeCount: int,
     *     currentStatus: string,
     *     servers: Collection<int, ApiServer>,
     *     unreadNotificationCount: int
     * }
     */
    private function dashboardPayload(Request $request, RentalService $rentalService): array
    {
        $user = $request->user();
        $rentalService->expireOverdueRentalsForUser($user->id);

        $status = (string) $request->query('status', 'active');
        $query = Rental::where('user_id', $user->id)->with('server')->latest();
        if ($request->filled('server')) {
            $query->where('server_id', $request->query('server'));
        }
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'completed') {
            $query->completed();
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $rentals = $query->paginate(15)
            ->withPath(route('dashboard'))
            ->withQueryString();

        return [
            'user' => $user,
            'rentals' => $rentals,
            'activeCount' => Rental::where('user_id', $user->id)->active()->count(),
            'currentStatus' => $status,
            'servers' => ApiServer::activeCached(),
            'unreadNotificationCount' => AppNotification::whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('read_at'))->count(),
        ];
    }

    /** Dismiss the login popup for this session (user clicked Disable/Close). */
    public function dismissLoginPopup(Request $request): JsonResponse
    {
        $request->session()->forget('show_login_popup');

        return response()->json(['ok' => true]);
    }
}

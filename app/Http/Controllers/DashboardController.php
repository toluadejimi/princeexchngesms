<?php

namespace App\Http\Controllers;

use App\Models\ApiServer;
use App\Models\Notification as AppNotification;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, RentalService $rentalService): View
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
        $rentals = $query->paginate(15)->withQueryString();

        $activeCount = Rental::where('user_id', $user->id)->active()->count();
        $servers = ApiServer::active()->orderBy('sort_order')->get();
        $unreadNotificationCount = AppNotification::whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('read_at'))->count();

        return view('dashboard.index', [
            'user' => $user,
            'rentals' => $rentals,
            'activeCount' => $activeCount,
            'servers' => $servers,
            'unreadNotificationCount' => $unreadNotificationCount,
        ]);
    }
}

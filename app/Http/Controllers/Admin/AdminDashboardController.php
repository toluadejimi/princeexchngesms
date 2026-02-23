<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $revenueByServer = Rental::query()
            ->whereIn('status', ['completed', 'active'])
            ->select('server_id', DB::raw('SUM(cost) as total'))
            ->groupBy('server_id')
            ->with('server:id,name')
            ->get()
            ->keyBy('server_id');

        $totalRentals = Rental::count();
        $activeRentals = Rental::active()->count();
        $totalRevenue = Rental::whereIn('status', [Rental::STATUS_COMPLETED, Rental::STATUS_ACTIVE])->sum('cost');
        $totalUsers = User::count();
        $totalWalletBalance = (float) User::sum('wallet_balance');
        $servers = ApiServer::withCount('rentals')->get();

        return view('admin.dashboard', [
            'revenueByServer' => $revenueByServer,
            'totalRentals' => $totalRentals,
            'activeRentals' => $activeRentals,
            'totalRevenue' => $totalRevenue,
            'totalUsers' => $totalUsers,
            'totalWalletBalance' => $totalWalletBalance,
            'servers' => $servers,
        ]);
    }
}

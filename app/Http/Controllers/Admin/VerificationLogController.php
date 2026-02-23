<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationLogController extends Controller
{
    /**
     * List all verification requests (rentals) for admin: user, country, service, number, status, cost, date.
     */
    public function index(Request $request): View
    {
        $query = Rental::with(['user:id,name,email', 'server:id,name,type'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('server')) {
            $query->where('server_id', $request->query('server'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $rentals = $query->paginate(30)->withQueryString();
        $servers = ApiServer::active()->orderBy('sort_order')->get(['id', 'name', 'type']);

        return view('admin.verifications.index', [
            'rentals' => $rentals,
            'servers' => $servers,
        ]);
    }
}

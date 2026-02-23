<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiServer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function index(): View
    {
        $servers = ApiServer::orderBy('sort_order')->get();
        return view('admin.servers.index', ['servers' => $servers]);
    }

    public function edit(ApiServer $server): View
    {
        return view('admin.servers.edit', ['server' => $server]);
    }

    public function update(Request $request, ApiServer $server): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|url',
            'api_key' => 'nullable|string',
            'type' => 'required|in:usa_only,multi_country',
            'profit_margin_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $validated['status'] = $request->boolean('status');
        if (!empty($validated['api_key'])) {
            $server->api_key = $validated['api_key'];
        }
        unset($validated['api_key']);
        $server->update($validated);
        return redirect()->route('admin.servers.index')->with('success', 'Server updated.');
    }

    /** Toggle server active status (for quick enable/disable from list). */
    public function toggle(ApiServer $server): RedirectResponse
    {
        $server->update(['status' => !$server->status]);
        $label = $server->status ? 'activated' : 'disabled';
        return redirect()->route('admin.servers.index')->with('success', "Server {$server->name} {$label}.");
    }
}

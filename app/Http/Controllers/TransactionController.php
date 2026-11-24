<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryTransaction $transaction): Response|RedirectResponse
    {
        $inventory = $transaction->inventory()->withTrashed()->firstOrFail();

        Gate::authorize('view', $inventory);

        $items = $transaction->items()
            ->with([
                'product' => fn ($query) => $query->withTrashed()->select('id', 'name'),
                'store' => fn ($query) => $query->withTrashed()->select('id', 'name'),
            ])
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        if ($items->currentPage() > $items->lastPage()) {
            return redirect()->to($items->url($items->lastPage()));
        }

        $inventory->load('owner:id,firstname,lastname');
        $transaction->load('user:id,firstname,lastname');

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
            'inventory' => $inventory->only(['id', 'name', 'owner']),
            'items' => $items,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryTransaction $inventoryTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryTransaction $inventoryTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        //
    }
}

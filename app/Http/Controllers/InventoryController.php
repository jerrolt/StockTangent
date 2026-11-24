<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventories.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Inventories/Index', [
            'inventories' => Inventory::visibleTo($request->user())
                    ->with(['owner:id,firstname,lastname', 'organization' => fn ($query) => $query->withTrashed()])
                    ->orderBy('name')
                    ->orderBy('inventories.id')
                    ->paginate(15)
                    ->withQueryString()
                    ->through(fn (Inventory $inventory) => $this->details($inventory)),

            'deletedInventories' => Inventory::visibleTo($request->user())
                    ->with(['owner:id,firstname,lastname', 'organization' => fn ($query) => $query->withTrashed()])
                    ->onlyTrashed()
                    ->orderBy('name')->orderBy('inventories.id')
                    ->paginate(15, ['*'], 'deleted_page')->withQueryString()
                    ->through(fn (Inventory $inventory) => $this->details($inventory)),
            
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'actionResult' => $request->session()->get('inventoryActionResult'),
        ]);
    }

    /**
     * Display the transactions for the specified inventory.
     */
    public function transactions(Inventory $inventory): Response
    {
        Gate::authorize('view', $inventory);

        $inventory->load([
            'transactions' => fn ($query) => $query->latest()->orderByDesc('id'),
            'transactions.items' => fn ($query) => $query->orderBy('id'),
            'transactions.items.product' => fn ($query) => $query->withTrashed()->select('id', 'name', 'unit_of_measure'),
        ]);

        return Inertia::render('Inventories/Transactions', ['inventory' => $this->details($inventory)]);
    }


    /** 
     * Display the items for the specified inventory.
     */
    public function items(Inventory $inventory): Response|RedirectResponse
    {
        Gate::authorize('view', $inventory);

        $items = $inventory->items()
            ->withTrashed()
            ->with([
                'product' => fn ($query) => $query->withTrashed()->select('id', 'name', 'sku', 'unit_of_measure'),
            ])
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        if ($items->currentPage() > $items->lastPage()) {
            return redirect()->to($items->url($items->lastPage()));
        }

        return Inertia::render('Inventories/Items', [
            'inventory' => $inventory->only(['id', 'name']),
            'items' => $items,
        ]);
    }
            

    /**
     * Display the specified inventory.
     */
    public function show(Inventory $inventory): Response
    {
        Gate::authorize('view', $inventory);

        $inventory->load(['items' => fn ($query) => $query->withTrashed()->orderBy('id')->with([
            'product' => fn ($query) => $query->withTrashed()->select('id', 'name', 'sku', 'unit_of_measure'),
        ])]);

        return Inertia::render('Inventories/Show',
            [
                'inventory' => $this->details($inventory)
            ]
        );
    }

    /**
     * Show the form for creating a new inventory.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Inventories/Create', ['organizations' => $request->user()->organizations()->orderBy('name')->get(['organizations.id', 'name'])]);
    }

    public function store(InventoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $inventory = DB::transaction(function () use ($request, $data) {
                $data['user_id'] = empty($data['organization_id']) ? $request->user()->id : null;
                $inventory = Inventory::create($data);
                $inventory->users()->attach($request->user()->id, ['permission' => 'admin']);

                return $inventory;
            });
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to create the inventory. Please try again.', successful: false);
        }

        return $this->actionResponse('Inventory created.', $inventory);
    }

    public function edit(Inventory $inventory): Response
    {
        Gate::authorize('update', $inventory);

        return Inertia::render('Inventories/Edit', ['inventory' => $this->details($inventory)]);
    }

    public function update(InventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'description']);

        try {
            $inventory->update($data);
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to update the inventory. Please try again.', $inventory, false);
        }

        return $this->actionResponse('Inventory updated.', $inventory);
    }

    public function destroy(Inventory $inventory): RedirectResponse
    {
        Gate::authorize('delete', $inventory);
        try {
            $inventory->delete();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to delete the inventory. Please try again.', $inventory, false, 'delete');
        }

        return $this->actionResponse($inventory->name.': Inventory deleted.', $inventory);
    }

    public function restore(Inventory $inventory): RedirectResponse
    {
        Gate::authorize('restore', $inventory);
        abort_unless($inventory->trashed(), 404);

        try {
            $inventory->restore();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to restore the inventory. Please try again.', $inventory, false, 'restore');
        }

        return $this->actionResponse($inventory->name.': Inventory restored.', $inventory);
    }

    private function details(Inventory $inventory): Inventory
    {
        $inventory->loadMissing([
            'owner:id,firstname,lastname', 
            'organization' => fn ($query) => $query->withTrashed(), 
            'items' => fn ($query) => $query->withTrashed()
                ->orderBy('id')
                ->with(['product' => fn ($query) => $query->withTrashed()->select('id', 'name', 'sku', 'unit_of_measure')]),
            'transactions',
            'users' => fn ($query) => $query->select('users.id', 'firstname', 'lastname'),
        ]);

        $inventory->setAttribute('can_update', Gate::allows('update', $inventory));
        $inventory->setAttribute('can_delete', Gate::allows('delete', $inventory));

        return $inventory;
    }

    private function actionResponse(string $message, ?Inventory $inventory = null, bool $successful = true, ?string $nextAction = null): RedirectResponse
    {
        return to_route('inventories.index')
            ->with($successful ? 'success' : 'error', $message)
            ->with('inventoryActionResult', [
                'id' => (string) Str::uuid(),
                'status' => $successful ? 'success' : 'error',
                'message' => $message,
                'inventory' => $inventory ? ['id' => $inventory->id, 'name' => $inventory->name] : null,
                'nextAction' => $inventory && Gate::allows('delete', $inventory) ? ($nextAction ?? ($inventory->trashed() ? 'restore' : 'delete')) : null,
            ]);
    }
}

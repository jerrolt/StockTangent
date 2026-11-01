<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Organizations/Index', [
            'organizations' => $request->user()->organizations()->orderBy('name')->orderBy('organizations.id')->paginate(15)->withQueryString(),
            'deletedOrganizations' => $request->user()->organizations()->onlyTrashed()->orderBy('name')->orderBy('organizations.id')->paginate(15, ['*'], 'deleted_page')->withQueryString(),
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'actionResult' => $request->session()->get('organizationActionResult'),
        ]);
    }

    public function show(Organization $organization): Response
    {
        Gate::authorize('view', $organization);

        return Inertia::render('Organizations/Show', ['organization' => $organization]);
    }

    public function create(): Response
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        try {
            $organization = DB::transaction(function () use ($request, $data) {
                $organization = Organization::create($data);
                $organization->users()->attach($request->user()->id);

                return $organization;
            });
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to create the organization. Please try again.', successful: false);
        }

        return $this->actionResponse('Organization created.', $organization);
    }

    public function edit(Organization $organization): Response
    {
        Gate::authorize('update', $organization);

        return Inertia::render('Organizations/Edit', ['organization' => $organization]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        Gate::authorize('update', $organization);
        $data = $this->validatedData($request);

        try {
            $organization->update($data);
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to update the organization. Please try again.', $organization, false);
        }

        return $this->actionResponse('Organization updated.', $organization);
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        Gate::authorize('delete', $organization);
        try {
            $organization->delete();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to delete the organization. Please try again.', $organization, false, 'delete');
        }

        return $this->actionResponse($organization->name.': Organization deleted.', $organization);
    }

    public function restore(Organization $organization): RedirectResponse
    {
        Gate::authorize('restore', $organization);
        abort_unless($organization->trashed(), 404);

        try {
            $organization->restore();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to restore the organization. Please try again.', $organization, false, 'restore');
        }

        return $this->actionResponse($organization->name.': Organization restored.', $organization);
    }

    private function actionResponse(string $message, ?Organization $organization = null, bool $successful = true, ?string $nextAction = null): RedirectResponse
    {
        return to_route('organizations.index')
            ->with($successful ? 'success' : 'error', $message)
            ->with('organizationActionResult', [
                'id' => (string) Str::uuid(),
                'status' => $successful ? 'success' : 'error',
                'message' => $message,
                'organization' => $organization ? ['id' => $organization->id, 'name' => $organization->name] : null,
                'nextAction' => $organization ? ($nextAction ?? ($organization->trashed() ? 'restore' : 'delete')) : null,
            ]);
    }

    private function validatedData(Request $request): array
    {
        if (is_string($request->input('state'))) {
            $request->merge(['state' => strtoupper(trim($request->input('state')))]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}

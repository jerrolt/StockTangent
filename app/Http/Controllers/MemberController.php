<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with([
            'organizations' => fn ($query) => $query->withTrashed(), 
            'inventories' => fn ($query) => $query->withTrashed()
        ])
        ->orderBy('users.lastname')
        ->orderBy('users.firstname')
        ->paginate(15)
        ->withQueryString();

        $deletedMembers = User::with([
            'organizations' => fn ($query) => $query->withTrashed(), 
            'inventories' => fn ($query) => $query->withTrashed()
        ])->onlyTrashed()
        ->orderBy('users.lastname')
        ->orderBy('users.firstname')
        ->paginate(15, ['*'], 'deleted_page')->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $users,
            'deletedMembers' => $deletedMembers,
            // 'success' => $request->session()->get('success'),
            // 'error' => $request->session()->get('error'),
            // 'actionResult' => $request->session()->get('memberActionResult'),
        ]);
    }


    /**
     * Show Members user profile. 
     *  - Includes soft deleted members.
     *  - Authorized users can view other members' profiles.
     * 
     */
    public function show(User $user): Response
    {
        // Gate::authorize('view', $user);
        // dd($user);
        $user->load([
            'organizations' => fn ($query) => $query->withTrashed(),
            'inventories' => fn ($query) => $query->withTrashed(),
        ]);

        return Inertia::render('Members/Show', ['member' => $user]);
    }



    // public function create(): Response
    // {
    //     return Inertia::render('Members/Create');
    // }


    // public function store(MemberRequest $request): RedirectResponse
    // {
    //     $data = $request->validated();

    //     try {
    //         $organization = DB::transaction(function () use ($request, $data) {
    //             $organization = Organization::create($data);
    //             $organization->users()->attach($request->user()->id);

    //             return $organization;
    //         });
    //     } catch (QueryException $exception) {
    //         report($exception);

    //         return $this->actionResponse('Unable to create the organization. Please try again.', successful: false);
    //     }

    //     return $this->actionResponse('Organization created.', $organization);
    // }


    // public function edit(User $user): Response
    // {
    //     // Temporarily disabled authorize
    //     // Gate::authorize('update', $user);
    //     return Inertia::render('Members/Edit', ['member' => $user]);
    // }



    // public function update(MemberRequest $request, User $user): RedirectResponse
    // {
    //     $data = $request->validated();

    //     try {
    //         $user->update($data);
    //     } catch (QueryException $exception) {
    //         report($exception);

    //         return $this->actionResponse('Unable to update the member. Please try again.', $user, false);
    //     }

    //     return $this->actionResponse('Member updated.', $user);
    // }



    public function destroy(User $member): RedirectResponse
    {
        Gate::authorize('delete', $member);
        try {
            $member->delete();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to delete the member. Please try again.', $member, false, 'delete');
        }

        return $this->actionResponse($member->name.': Member account disabled.', $member);
    }


    public function restore(User $member): RedirectResponse
    {
        Gate::authorize('restore', $member);
        abort_unless($member->trashed(), 404);

        try {
            $member->restore();
        } catch (QueryException $exception) {
            report($exception);

            return $this->actionResponse('Unable to restore the member. Please try again.', $member, false, 'restore');
        }

        return $this->actionResponse($member->name.': Member account restored.', $member);
    }


    private function actionResponse(string $message, ?User $member = null, bool $successful = true, ?string $nextAction = null): RedirectResponse
    {
        return to_route('members.index')
            ->with($successful ? 'success' : 'error', $message)
            ->with('memberActionResult', [
                'id' => (string) Str::uuid(),
                'status' => $successful ? 'success' : 'error',
                'message' => $message,
                'member' => $member ? ['id' => $member->id, 'name' => $member->name] : null,
                'nextAction' => $member ? ($nextAction ?? ($member->trashed() ? 'restore' : 'delete')) : null,
            ]);
    }
}

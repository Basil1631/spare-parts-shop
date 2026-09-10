<?php

namespace App\Http\Controllers;

use App\Enums\StaffRole;
use App\Models\Branch;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageStaff(), 403);

        $users = User::query()
            ->with(['roles', 'branch'])
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->orderBy('name')
            ->paginate(30);

        return view('staff.index', compact('users'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canManageStaff(), 403);

        return view('staff.form', [
            'member' => null,
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageStaff(), 403);
        $data = $this->validated($request);
        $role = $data['role'];
        unset($data['role'], $data['password_confirmation']);
        $data['monthly_salary_fils'] = Money::toFils($data['monthly_salary']);
        unset($data['monthly_salary']);
        if (! $request->user()->isAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
            if (! in_array($role, ['sales', 'purchase', 'accountant'], true)) {
                abort(403);
            }
        }
        $user = User::query()->create($data);
        $user->syncRoles([Role::findOrCreate($role, 'web')]);

        return redirect()->route('staff.index')->with('status', 'Staff account created. They use the same login page with their email and password.');
    }

    public function edit(Request $request, User $staff): View
    {
        $this->assertCanEdit($request->user(), $staff);

        return view('staff.form', [
            'member' => $staff,
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->assertCanEdit($request->user(), $staff);
        $data = $this->validated($request, $staff->id);
        $role = $data['role'];
        unset($data['role'], $data['password_confirmation']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $data['monthly_salary_fils'] = Money::toFils($data['monthly_salary']);
        unset($data['monthly_salary']);
        $data['is_active'] = $request->boolean('is_active', true);
        if (! $request->user()->isAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }
        $staff->update($data);
        if ($request->user()->isAdmin() || in_array($role, ['sales', 'purchase', 'accountant'], true)) {
            $staff->syncRoles([Role::findOrCreate($role, 'web')]);
        }

        return redirect()->route('staff.index')->with('status', 'Staff updated.');
    }

    private function assertCanEdit(User $actor, User $staff): void
    {
        abort_unless($actor->canManageStaff(), 403);
        if (! $actor->isAdmin() && $staff->branch_id !== $actor->branch_id) {
            abort(403);
        }
        if ($staff->isAdmin() && ! $actor->isAdmin()) {
            abort(403);
        }
    }

    /** @return list<StaffRole> */
    private function assignableRoles(User $actor): array
    {
        if ($actor->isAdmin()) {
            return [StaffRole::BranchManager, StaffRole::Sales, StaffRole::Purchase, StaffRole::Accountant];
        }

        return StaffRole::branchStaff();
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $roles = $request->user()->isAdmin()
            ? ['branch_manager', 'sales', 'purchase', 'accountant']
            : ['sales', 'purchase', 'accountant'];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.($id ?: 'NULL')],
            'role' => ['required', Rule::in($roles)],
            'branch_id' => [$request->user()->isAdmin() ? 'required' : 'nullable', 'exists:branches,id'],
            'monthly_salary' => ['required', 'numeric', 'min:0'],
            'incentive_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];

        return $request->validate($rules);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $this->authorize('admin-only');

        return view('branches.index', [
            'branches' => Branch::query()->with('manager')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('admin-only');

        return view('branches.form', ['branch' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('admin-only');
        Branch::query()->create($this->validated($request));

        return redirect()->route('branches.index')->with('status', 'Branch added.');
    }

    public function edit(Branch $branch): View
    {
        $this->authorize('admin-only');

        return view('branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('admin-only');
        $data = $this->validated($request, $branch->id);
        $data['active'] = $request->boolean('active', true);
        $branch->update($data);

        return redirect()->route('branches.index')->with('status', 'Branch updated.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code,'.($id ?: 'NULL')],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}

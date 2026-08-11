<?php

namespace App\Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Http\Requests\StorePositionRequest;
use App\Modules\HR\Http\Requests\UpdatePositionRequest;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Position;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')->orderBy('title')->paginate(20);

        return view('hr::positions.index', compact('positions'));
    }

    public function create()
    {
        $this->authorize('create', Position::class);

        $departments = Department::orderBy('name')->get();

        return view('hr::positions.create', compact('departments'));
    }

    public function store(StorePositionRequest $request)
    {
        Position::create($request->validated());

        return redirect()->route('hr.positions.index')
            ->with('success', 'Position berhasil dibuat.');
    }

    public function edit(Position $position)
    {
        $this->authorize('update', $position);

        $departments = Department::orderBy('name')->get();

        return view('hr::positions.edit', compact('position', 'departments'));
    }

    public function update(UpdatePositionRequest $request, Position $position)
    {
        $position->update($request->validated());

        return redirect()->route('hr.positions.index')
            ->with('success', 'Position berhasil diperbarui.');
    }
}

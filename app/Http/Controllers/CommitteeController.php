<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index()
    {
        $committees = Committee::withCount('voters')->get();
        return view('committees.index', compact('committees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable'
        ]);
        Committee::create($validated);
        return redirect()->route('committees.index')->with('success', 'تم إضافة اللجنة بنجاح');
    }

    public function update(Request $request, Committee $committee)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable'
        ]);
        $committee->update($validated);
        return redirect()->route('committees.index')->with('success', 'تم تحديث اللجنة بنجاح');
    }

    public function destroy(Committee $committee)
    {
        $committee->delete();
        return redirect()->route('committees.index')->with('success', 'تم حذف اللجنة بنجاح');
    }
}
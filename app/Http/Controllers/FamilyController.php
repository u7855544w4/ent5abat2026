<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index()
    {
        $families = Voter::select('family_name')
            ->selectRaw('COUNT(*) as voters_count')
            ->whereNotNull('family_name')
            ->where('family_name', '!=', '')
            ->groupBy('family_name')
            ->get()
            ->map(function($f) {
                return (object)['id' => $f->family_name, 'name' => $f->family_name, 'voters_count' => $f->voters_count];
            });
            
        return view('families.index', compact('families'));
    }

    public function store(Request $request)
    {
        // Families are auto-created from voters, no need to store separately
        return redirect()->route('families.index')->with('success', 'تم إضافة العائلة بنجاح');
    }

    public function update(Request $request, $id)
    {
        // Update family_name in all voters
        Voter::where('family_name', $id)->update(['family_name' => $request->name]);
        return redirect()->route('families.index')->with('success', 'تم تحديث العائلة بنجاح');
    }

    public function destroy($id)
    {
        // Don't delete, just inform user
        return redirect()->route('families.index')->with('error', 'لا يمكن حذف العائلات');
    }
}
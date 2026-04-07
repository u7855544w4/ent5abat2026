<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use App\Models\Family;
use Illuminate\Http\Request;

class VoterController extends Controller
{
    public function index(Request $request)
    {
        $query = Voter::with('committee');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('electoral_code', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->family) {
            $query->where('family_name', $request->family);
        }
        
        if ($request->center) {
            $query->where('electoral_center', $request->center);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $voters = $query->orderBy('family_name')->paginate(20);
        
        $families = Voter::select('family_name')
            ->whereNotNull('family_name')
            ->where('family_name', '!=', '')
            ->groupBy('family_name')
            ->pluck('family_name');
            
        $centers = Voter::select('electoral_center')
            ->whereNotNull('electoral_center')
            ->where('electoral_center', '!=', '')
            ->groupBy('electoral_center')
            ->pluck('electoral_center');
        
        return view('voters.index', compact('voters', 'families', 'centers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required',
            'family_name' => 'nullable',
            'electoral_code' => 'nullable',
            'electoral_center' => 'nullable',
            'status' => 'nullable'
        ]);
        
        Voter::create($validated);
        return redirect()->route('voters.index')->with('success', 'تم إضافة الناخب بنجاح');
    }

    public function update(Request $request, Voter $voter)
    {
        $validated = $request->validate([
            'full_name' => 'required',
            'family_name' => 'nullable',
            'electoral_code' => 'nullable',
            'electoral_center' => 'nullable',
            'status' => 'nullable',
            'follower_name' => 'nullable',
            'notes' => 'nullable'
        ]);
        
        $voter->update($validated);
        return redirect()->route('voters.index')->with('success', 'تم تحديث الناخب بنجاح');
    }

    public function destroy(Voter $voter)
    {
        $voter->delete();
        return redirect()->route('voters.index')->with('success', 'تم حذف الناخب بنجاح');
    }
}
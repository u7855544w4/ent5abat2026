<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use App\Models\Committee;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Voter::with('committee');
        
        if ($request->family) {
            $query->where('family_name', $request->family);
        }
        
        if ($request->center) {
            $query->where('electoral_center', $request->center);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $voters = $query->orderBy('family_name')->orderBy('electoral_center')->get();
        
        $families = Voter::select('family_name')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('family_name')
            ->where('family_name', '!=', '')
            ->groupBy('family_name')
            ->get();
            
        $centers = Voter::select('electoral_center')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('electoral_center')
            ->where('electoral_center', '!=', '')
            ->groupBy('electoral_center')
            ->get();
        
        $committees = Committee::withCount('voters')->get();
        
        return view('tasks.index', compact('voters', 'families', 'centers', 'committees'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'voter_ids' => 'required|array',
            'committee_id' => 'required|exists:committees,id'
        ]);
        
        Voter::whereIn('id', $validated['voter_ids'])->update([
            'committee_id' => $validated['committee_id']
        ]);
        
        return redirect()->route('tasks.index')->with('success', 'تم توزيع ' . count($validated['voter_ids']) . ' ناخب على اللجنة بنجاح');
    }
}
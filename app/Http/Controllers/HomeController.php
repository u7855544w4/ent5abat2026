<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use App\Models\Committee;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalVoters = Voter::count();
        $positiveCount = Voter::where('status', 'positive')->count();
        $uncertainCount = Voter::where('status', 'uncertain')->count();
        $negativeCount = Voter::where('status', 'negative')->count();
        $pendingCount = Voter::where('status', 'pending')->count();
        
        // Families stats (from voters table)
        $familiesStats = Voter::select('family_name')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive")
            ->selectRaw("SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain")
            ->selectRaw("SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative")
            ->whereNotNull('family_name')
            ->where('family_name', '!=', '')
            ->groupBy('family_name')
            ->get();
        
        // Centers stats
        $centersStats = Voter::select('electoral_center')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive")
            ->selectRaw("SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain")
            ->selectRaw("SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative")
            ->whereNotNull('electoral_center')
            ->where('electoral_center', '!=', '')
            ->groupBy('electoral_center')
            ->get();
        
        // Committees with counts
        $committees = Committee::withCount('voters')->get();
        
        return view('index', compact(
            'totalVoters', 'positiveCount', 'uncertainCount', 'negativeCount', 'pendingCount',
            'familiesStats', 'centersStats', 'committees'
        ));
    }
}
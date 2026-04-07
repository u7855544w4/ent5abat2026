<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use App\Models\Committee;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Families reports
        $familiesStats = Voter::select('family_name')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive")
            ->selectRaw("SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain")
            ->selectRaw("SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative")
            ->whereNotNull('family_name')
            ->where('family_name', '!=', '')
            ->groupBy('family_name')
            ->get()
            ->map(function($f) {
                $f->success_rate = $f->total > 0 ? round(($f->positive / $f->total) * 100, 1) : 0;
                return $f;
            });
        
        // Centers reports
        $centersStats = Voter::select('electoral_center')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive")
            ->selectRaw("SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain")
            ->selectRaw("SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative")
            ->whereNotNull('electoral_center')
            ->where('electoral_center', '!=', '')
            ->groupBy('electoral_center')
            ->get()
            ->map(function($c) {
                $c->success_rate = $c->total > 0 ? round(($c->positive / $c->total) * 100, 1) : 0;
                return $c;
            });
        
        // Committees reports
        $committeesStats = Committee::withCount(['voters' => function($q) {
            $q->selectRaw("SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive")
              ->selectRaw("SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain")
              ->selectRaw("SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative");
        }])->get();
        
        return view('reports.index', compact('familiesStats', 'centersStats', 'committeesStats'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header row
        array_shift($rows);

        $count = 0;
        foreach ($rows as $row) {
            if (!empty($row[0])) { // full_name required
                Voter::create([
                    'full_name' => $row[0],
                    'family_name' => $row[1] ?? null,
                    'electoral_code' => $row[2] ?? null,
                    'electoral_center' => $row[3] ?? null,
                    'status' => 'pending'
                ]);
                $count++;
            }
        }

        return redirect()->route('home')->with('success', "تم استيراد {$count} ناخب بنجاح");
    }

    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'الاسم بالكامل');
        $sheet->setCellValue('B1', 'اسم العائلة');
        $sheet->setCellValue('C1', 'الرمز انتخابي');
        $sheet->setCellValue('D1', 'المركز انتخابي');
        
        $sheet->setCellValue('A2', 'أحمد كريم');
        $sheet->setCellValue('B2', 'كريم');
        $sheet->setCellValue('C2', '12345');
        $sheet->setCellValue('D2', 'مدرسة الشهيد عبد القادر');
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="نموذج_استيراد.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}
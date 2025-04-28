<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import');
    }

    public function upload(Request $request, ImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        $path = $request->file('file')->store('imports');

        $importService->import($path);

        return response()->json([
            'success' => true,
            'message' => 'Импорт успешно завершён'
        ], 200);
    }
}

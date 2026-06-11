<?php

namespace App\Http\Controllers;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup.index');
    }

    public function download(string $filename)
    {
        // Security: only allow .sqlite files, no path traversal
        if (! preg_match('/^[\w\-\.]+\.sqlite$/', $filename)) {
            abort(403);
        }

        $filePath = storage_path('app/backups/'.$filename);

        if (! file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function database(): BinaryFileResponse
    {
        $db = config('database.connections.'.config('database.default'));
        $path = tempnam(sys_get_temp_dir(), 'backup');

        // Password lewat env agar tidak terlihat di daftar proses.
        $result = Process::timeout(300)
            ->env(['MYSQL_PWD' => (string) ($db['password'] ?? '')])
            ->run(array_filter([
                'mysqldump',
                '--host='.($db['host'] ?? ''),
                '--port='.($db['port'] ?? ''),
                ! empty($db['unix_socket']) ? '--socket='.$db['unix_socket'] : null,
                '--user='.($db['username'] ?? ''),
                '--single-transaction',
                '--triggers',
                '--result-file='.$path,
                $db['database'],
            ]));

        // Dump gagal tidak boleh terunduh sebagai backup yang tampak valid.
        if ($result->failed()) {
            @unlink($path);
            logger()->error('Backup database gagal', ['error' => $result->errorOutput()]);
            abort(500, 'Backup database gagal. Cek log server.');
        }

        return response()
            ->download($path, 'backup-'.basename($db['database']).'-'.now()->format('Y-m-d_His').'.sql', [
                'Content-Type' => 'application/sql',
            ])
            ->deleteFileAfterSend();
    }
}

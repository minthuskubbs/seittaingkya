<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:backup.manage');
    }

    private function backupDir(): string
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    public function index()
    {
        $files = collect(File::files($this->backupDir()))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->map(fn ($f) => [
                'name' => $f->getFilename(),
                'size' => $f->getSize(),
                'date' => date('Y-m-d H:i', $f->getMTime()),
            ])->values();

        return view('backup.index', compact('files'));
    }

    public function create()
    {
        $db = config('database.connections.mysql');
        $file = $this->backupDir().DIRECTORY_SEPARATOR.'backup_'.date('Ymd_His').'.sql';
        $dump = config('app.mysqldump_path', 'C:\\xampp\\mysql\\bin\\mysqldump.exe');

        $args = [$dump, '-h', $db['host'], '-P', (string) $db['port'], '-u', $db['username']];
        if (! empty($db['password'])) {
            $args[] = '-p'.$db['password'];
        }
        $args[] = $db['database'];

        $process = new Process($args);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->withErrors(['backup' => 'Backup failed: '.trim($process->getErrorOutput() ?: 'mysqldump not found.')]);
        }

        File::put($file, $process->getOutput());
        AuditLog::record('backup', 'Created database backup '.basename($file));

        return back()->with('status', 'Backup created: '.basename($file));
    }

    public function download(string $file)
    {
        $path = $this->backupDir().DIRECTORY_SEPARATOR.basename($file);
        abort_unless(File::exists($path), 404);

        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate(['sql_file' => 'required|file|mimes:sql,txt']);

        $db = config('database.connections.mysql');
        $client = config('app.mysql_path', 'C:\\xampp\\mysql\\bin\\mysql.exe');
        $tmp = $request->file('sql_file')->getRealPath();

        $args = [$client, '-h', $db['host'], '-P', (string) $db['port'], '-u', $db['username']];
        if (! empty($db['password'])) {
            $args[] = '-p'.$db['password'];
        }
        $args[] = $db['database'];

        $process = Process::fromShellCommandline(
            implode(' ', array_map('escapeshellarg', $args)).' < '.escapeshellarg($tmp)
        );
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->withErrors(['restore' => 'Restore failed: '.trim($process->getErrorOutput() ?: 'mysql client not found.')]);
        }

        AuditLog::record('restore', 'Restored database from uploaded file');

        return back()->with('status', 'Database restored.');
    }
}

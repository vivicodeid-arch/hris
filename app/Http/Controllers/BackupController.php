<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index()
    {
        return view('utilities.backup.index');
    }

    public function download()
    {
        try {
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $date = date('Y-m-d_His');
            $fileName = "backup_{$date}.sql";
            $filePath = $backupDir . '/' . $fileName;

            // Try mysqldump first
            $hasMysqldump = false;
            exec('which mysqldump', $output, $returnVar);
            if ($returnVar === 0) {
                $hasMysqldump = true;
            } else {
                if (file_exists('/usr/bin/mysqldump') && is_executable('/usr/bin/mysqldump')) {
                    $hasMysqldump = true;
                } elseif (file_exists('/bin/mysqldump') && is_executable('/bin/mysqldump')) {
                    $hasMysqldump = true;
                }
            }

            if ($hasMysqldump) {
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port', 3306);

                $command = "mysqldump --no-tablespaces --column-statistics=0 -h {$dbHost} -P {$dbPort} -u {$dbUser} -p{$dbPass} {$dbName} > '{$filePath}' 2>&1";
                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !File::exists($filePath) || File::size($filePath) === 0) {
                    $this->backupDatabasePHP($filePath);
                }
            } else {
                $this->backupDatabasePHP($filePath);
            }

            if (File::exists($filePath)) {
                return response()->download($filePath)->deleteFileAfterSend(true);
            } else {
                return redirect()->back()->with(['error' => 'Gagal membuat file backup.']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    protected function backupDatabasePHP(string $filePath)
    {
        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \Exception("Gagal membuat file backup di {$filePath}");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $table) {
            $tableArray = (array)$table;
            $tableName = array_values($tableArray)[0];

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = ((array)$createTable[0])['Create Table'];

            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createTableSql . ";\n\n");

            DB::table($tableName)->orderByRaw('1')->chunk(200, function ($rows) use ($handle, $tableName) {
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? "NULL" : "'" . addslashes($value) . "'";
                    }, (array)$row);
                    
                    $sql = "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n";
                    fwrite($handle, $sql);
                }
            });
            
            fwrite($handle, "\n\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:512000' 
        ]);

        try {
            $file = $request->file('backup_file');
            $path = $file->getRealPath();

            $hasMysql = false;
            exec('which mysql', $output, $returnVar);
            if ($returnVar === 0) {
                $hasMysql = true;
            } else {
                if (file_exists('/usr/bin/mysql') && is_executable('/usr/bin/mysql')) {
                    $hasMysql = true;
                } elseif (file_exists('/bin/mysql') && is_executable('/bin/mysql')) {
                    $hasMysql = true;
                }
            }

            if ($hasMysql) {
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port', 3306);
                
                $passString = empty($dbPass) ? '' : "-p{$dbPass}";
                $command = "mysql -h {$dbHost} -P {$dbPort} -u {$dbUser} {$passString} {$dbName} < '{$path}' 2>&1";
                exec($command, $output, $returnVar);

                if ($returnVar !== 0) {
                    $this->restoreDatabasePHP($path);
                }
            } else {
                $this->restoreDatabasePHP($path);
            }

            return redirect()->back()->with(['success' => 'Database berhasil direstore.']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Gagal merestore database: ' . $e->getMessage()]);
        }
    }

    protected function restoreDatabasePHP(string $filePath)
    {
        $sql = file_get_contents($filePath);
        if (!$sql) {
            throw new \Exception("Gagal membaca file {$filePath}");
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared($sql);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class BackupPersonalizado extends Command
{
    protected $signature = 'backup:personalizado {--only-db : Backup apenas do banco de dados}';
    protected $description = 'Realiza backup personalizado do sistema';

    public function handle()
    {
        $dataAtual = now()->format('Y-m-d_H-i-s');
        $nomeBackup = "backup_barbearia_{$dataAtual}";
        $diretorioBackup = storage_path("app/backups/{$nomeBackup}");
        
        // Criar diretório de backup
        if (!file_exists($diretorioBackup)) {
            mkdir($diretorioBackup, 0755, true);
        }
        
        $this->info("Iniciando backup: {$nomeBackup}");
        
        // 1. Backup do banco de dados
        $this->backupBancoDados($diretorioBackup);
        
        // 2. Backup de arquivos (se não for apenas DB)
        if (!$this->option('only-db')) {
            $this->backupArquivos($diretorioBackup);
            $this->backupConfiguracoes($diretorioBackup);
        }
        
        // 3. Compactar backup
        $this->compactarBackup($diretorioBackup, $nomeBackup);
        
        // 4. Limpar backups antigos
        $this->limparBackupsAntigos();
        
        $this->info("✅ Backup concluído com sucesso!");
        
        return Command::SUCCESS;
    }
    
    private function backupBancoDados($diretorio)
    {
        $this->info("Fazendo backup do banco de dados...");
        
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $arquivoSql = "{$diretorio}/database.sql";
        
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            $host,
            $username,
            $password,
            $database,
            $arquivoSql
        );
        
        exec($command);
        
        if (file_exists($arquivoSql) && filesize($arquivoSql) > 0) {
            $this->info("✅ Backup do banco realizado: " . $this->formatarBytes(filesize($arquivoSql)));
        } else {
            $this->error("❌ Falha no backup do banco de dados");
        }
    }
    
    private function backupArquivos($diretorio)
    {
        $this->info("Fazendo backup de arquivos importantes...");
        
        // Backup do storage
        $storageDir = "{$diretorio}/storage";
        mkdir($storageDir, 0755, true);
        
        // Copiar apenas arquivos importantes do storage
        $arquivosImportantes = [
            'storage/app/public' => 'public_uploads',
            'storage/logs' => 'logs',
        ];
        
        foreach ($arquivosImportantes as $origem => $destino) {
            if (is_dir($origem)) {
                $this->copiarDiretorio($origem, "{$storageDir}/{$destino}");
            }
        }
        
        // Backup do .env
        if (file_exists('.env')) {
            copy('.env', "{$diretorio}/.env");
        }
        
        $this->info("✅ Backup de arquivos realizado");
    }
    
    private function backupConfiguracoes($diretorio)
    {
        $this->info("Fazendo backup de configurações...");
        
        $configDir = "{$diretorio}/config";
        mkdir($configDir, 0755, true);
        
        // Copiar arquivos de configuração importantes
        $configFiles = [
            'config/app.php',
            'config/database.php',
            'config/filament.php',
            'config/filesystems.php',
            'config/mail.php',
        ];
        
        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                copy($configFile, "{$configDir}/" . basename($configFile));
            }
        }
        
        $this->info("✅ Backup de configurações realizado");
    }
    
    private function compactarBackup($diretorio, $nomeBackup)
    {
        $this->info("Compactando backup...");
        
        $zip = new ZipArchive();
        $zipFile = storage_path("app/backups/{$nomeBackup}.zip");
        
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($diretorio),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($diretorio) + 1);
                    
                    $zip->addFile($filePath, $relativePath);
                }
            }
            
            $zip->close();
            
            // Remover diretório não compactado
            $this->removerDiretorio($diretorio);
            
            $tamanho = $this->formatarBytes(filesize($zipFile));
            $this->info("✅ Backup compactado: {$nomeBackup}.zip ({$tamanho})");
        } else {
            $this->error("❌ Falha ao compactar backup");
        }
    }
    
    private function limparBackupsAntigos()
    {
        $this->info("Limpando backups antigos...");
        
        $backupDir = storage_path('app/backups');
        $limiteDias = 30; // Manter backups por 30 dias
        
        if (is_dir($backupDir)) {
            $files = glob("{$backupDir}/*.zip");
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $tempoCriacao = filectime($file);
                    $diferencaDias = (time() - $tempoCriacao) / (60 * 60 * 24);
                    
                    if ($diferencaDias > $limiteDias) {
                        unlink($file);
                        $this->info("🗑️  Removido backup antigo: " . basename($file));
                    }
                }
            }
        }
    }
    
    private function copiarDiretorio($origem, $destino)
    {
        if (!is_dir($destino)) {
            mkdir($destino, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($origem, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($destino . '/' . $iterator->getSubPathName());
            } else {
                copy($item, $destino . '/' . $iterator->getSubPathName());
            }
        }
    }
    
    private function removerDiretorio($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            
            if (is_dir($path)) {
                $this->removerDiretorio($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    private function formatarBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
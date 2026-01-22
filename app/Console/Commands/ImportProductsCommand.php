<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ImportHistory;
use App\Jobs\ProcessFoodFileJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from Open Food Facts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Starting import process...');

        try {
            // Busco o index.txt para saber exatamente quais arquivos preciso baixar.
            // É melhor do que colocar nomes fixos (hardcoded) no código.
            $indexUrl = "https://challenges.coode.sh/food/data/json/index.txt";
            $response = Http::get($indexUrl);

            if ($response->failed()) {
                throw new \Exception("Failed to fetch index.txt");
            }

            $files = array_filter(explode("\n", $response->body()));
            
            foreach ($files as $filename) {
                $filename = trim($filename);
                if (empty($filename)) continue;

                $this->info("Dispatching job for: {$filename}");
                // Eu não processo o arquivo aqui dentro do comando.
                // Eu jogo para uma fila (Redis) para que o sistema de filas do Laravel cuide da carga.
                ProcessFoodFileJob::dispatch($filename);
            }

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2) . 's';

            // Esse registro aqui serve para o endpoint do Health Check saber quando foi a última execução.
            ImportHistory::create([
                'status' => 'success',
                'imported_at' => now(),
                'processed_files' => $files,
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'execution_time' => $executionTime,
            ]);

            $this->info('Import process dispatched successfully!');

        } catch (\Exception $e) {
            Log::error("Import Command Failed: " . $e->getMessage());
            
            ImportHistory::create([
                'status' => 'failure',
                'imported_at' => now(),
                'error' => $e->getMessage(),
            ]);

            $this->error('Import process failed: ' . $e->getMessage());
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

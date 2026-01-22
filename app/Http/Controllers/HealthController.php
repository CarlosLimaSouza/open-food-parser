<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ImportHistory;
use Illuminate\Support\Facades\DB;

use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/',
        summary: 'Retorna o status da API',
        tags: ['Health'],
        security: [['ApiKeyAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Dados de saúde do sistema'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function index()
    {
        // Pego a última entrada da tabela de históricos que criei para mostrar no JSON.
        $lastCron = ImportHistory::latest()->first();
        
        $dbStatus = 'Error';
        try {
            // Um simples check de conexão com o banco para garantir que está tudo ok.
            DB::connection()->getPdo();
            $dbStatus = 'OK';
        } catch (\Exception $e) {}

        $uptime = $this->getUptime();

        return response()->json([
            'database' => $dbStatus,
            'last_cron_execution' => $lastCron ? $lastCron->imported_at->toDateTimeString() : 'Never',
            'online_time' => $uptime,
            'memory_usage' => $this->formatBytes(memory_get_usage()),
        ]);
    }

    private function getUptime()
    {
        // Calculo simples de uptime
        // Para um uptime real de servidor, seria algo melhor que isso.
        $startTime = $_SERVER['REQUEST_TIME'];
        $now = time();
        $diff = $now - $startTime;

        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;

        return sprintf('%dd %dh %dm %ds', $days, $hours, $minutes, $seconds);
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

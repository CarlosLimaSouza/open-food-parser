<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Aqui eu verifico o header customizado. 
        // Escolhi 'x-api-key'.
        $apiKey = $request->header('x-api-key');

        if (!$apiKey || $apiKey !== config('app.api_key')) {
            // Se a chave não bater ou não existir, já corto a conexão aqui com 401.
            return response()->json(['message' => 'Unauthorized. Please provide a valid API Key.'], 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getMethod() == "OPTIONS") {
            return response()
                ->json([], 204) // Ответ для OPTIONS
                ->header('Access-Control-Allow-Origin', 'https://exobloom.ru/')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

//        return response()->json($request->getHost());
        return $next($request)
            ->header('Access-Control-Allow-Origin', 'https://exobloom.ru') // Разрешаем только ваш фронтенд
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS') // Разрешаем эти методы
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization') // Разрешаем эти заголовки
            ->header('Access-Control-Allow-Credentials', 'true'); // Разрешаем куки;
    }

}

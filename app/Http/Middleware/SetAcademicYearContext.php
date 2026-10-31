<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Http\Request;

class SetAcademicYearContext
{
    /**
     * Handle an incoming request and set academic year context from header.
     */
    public function handle(Request $request, Closure $next)
    {
        $selectedYearId = $request->header('X-Academic-Year-Id');

        if ($selectedYearId && is_numeric($selectedYearId)) {
            $yearId = (int) $selectedYearId;

            // Merge into request input if not explicitly provided in URL or body
            if (!$request->has('academic_year_id')) {
                $request->merge(['academic_year_id' => $yearId]);
            }

            $activeYearId = AcademicYear::where('is_active', true)->value('id');
            $request->attributes->set('academic_year_id', $yearId);
            $request->attributes->set('is_archive_mode', $activeYearId && $yearId !== (int) $activeYearId);
        }

        return $next($request);
    }
}

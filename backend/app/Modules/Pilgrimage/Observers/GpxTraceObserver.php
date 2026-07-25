<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Observers;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Services\GpxSimplificationService;
use Illuminate\Support\Facades\Log;

/**
 * Observer GpxTrace — invalide le cache Redis simplifié à chaque save (ADR-U05).
 */
final class GpxTraceObserver
{
    public function __construct(
        private readonly GpxSimplificationService $simplification
    ) {}

    public function saved(GpxTrace $trace): void
    {
        $this->simplification->invalidateCache($trace);
        Log::info('GpxTrace saved — cache Redis simplifié invalidé', ['trace_id' => $trace->id]);
    }

    public function deleted(GpxTrace $trace): void
    {
        $this->simplification->invalidateCache($trace);
        Log::info('GpxTrace deleted — cache Redis simplifié invalidé', ['trace_id' => $trace->id]);
    }
}

<?php

namespace App\Models\Traits;

use App\Services\AuditLoggerService;

trait Auditable
{
    /**
     * Boot the auditable trait
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $auditLogger = app(AuditLoggerService::class);
            $auditLogger->logCreate($model);
        });

        static::updated(function ($model) {
            $auditLogger = app(AuditLoggerService::class);
            $oldValues = $model->getOriginal();
            $auditLogger->logUpdate($model, $oldValues);
        });

        static::deleted(function ($model) {
            $auditLogger = app(AuditLoggerService::class);
            $auditLogger->logDelete($model);
        });
    }

    /**
     * Get audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(\App\Models\AuditLog::class, 'auditable')
            ->orderBy('created_at', 'desc');
    }
}

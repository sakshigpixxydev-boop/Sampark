<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLoggerService
{
    /**
     * Log a create event
     */
    public function logCreate(Model $model, ?string $description = null, ?array $metadata = null): AuditLog
    {
        return $this->log(
            'CREATE',
            $model,
            $description ?? "Created {$this->getModelName($model)}",
            null,
            $model->getAttributes(),
            $metadata
        );
    }

    /**
     * Log an update event
     */
    public function logUpdate(Model $model, array $oldValues, ?string $description = null, ?array $metadata = null): AuditLog
    {
        return $this->log(
            'UPDATE',
            $model,
            $description ?? "Updated {$this->getModelName($model)}",
            $oldValues,
            $model->getAttributes(),
            $metadata
        );
    }

    /**
     * Log a delete event
     */
    public function logDelete(Model $model, ?string $description = null, ?array $metadata = null): AuditLog
    {
        return $this->log(
            'DELETE',
            $model,
            $description ?? "Deleted {$this->getModelName($model)}",
            $model->getAttributes(),
            null,
            $metadata
        );
    }

    /**
     * Log an authentication event
     */
    public function logAuth(string $eventType, ?int $userId = null, ?string $description = null, ?array $metadata = null): AuditLog
    {
        return AuditLog::create([
            'event_type' => $eventType,
            'auditable_type' => null, // Auth events don't have an auditable entity
            'auditable_id' => null,
            'user_id' => $userId ?? Auth::id(),
            'user_type' => 'App\Models\SysUser',
            'description' => $description ?? $eventType,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log a payment transaction
     */
    public function logPayment(Model $payment, string $eventType, ?string $description = null, ?array $metadata = null): AuditLog
    {
        return $this->log(
            $eventType,
            $payment,
            $description ?? "Payment {$eventType}",
            null,
            $payment->getAttributes(),
            $metadata
        );
    }

    /**
     * Log a custom event
     */
    public function logCustom(
        string $eventType,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        return $this->log(
            $eventType,
            $model,
            $description,
            $oldValues,
            $newValues,
            $metadata
        );
    }

    /**
     * Core logging method
     */
    protected function log(
        string $eventType,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        // Filter sensitive data from values
        $oldValues = $this->filterSensitiveData($oldValues);
        $newValues = $this->filterSensitiveData($newValues);

        return AuditLog::create([
            'event_type' => $eventType,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model ? $model->getKey() : null,
            'user_id' => Auth::id(),
            'user_type' => Auth::check() ? get_class(Auth::user()) : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Filter sensitive data from logged values
     */
    protected function filterSensitiveData(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitiveFields = ['password', 'pswd', 'pin', 'token', 'secret', 'api_key'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get a human-readable model name
     */
    protected function getModelName(Model $model): string
    {
        $className = class_basename($model);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $className));
    }

    /**
     * Get audit logs for a specific model
     */
    public function getLogsForModel(Model $model, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::forModel(get_class($model), $model->getKey())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs
     */
    public function getRecentLogs(int $limit = 100, ?string $eventType = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($eventType) {
            $query->ofType($eventType);
        }

        return $query->get();
    }

    /**
     * Get audit logs for a specific user
     */
    public function getLogsForUser(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

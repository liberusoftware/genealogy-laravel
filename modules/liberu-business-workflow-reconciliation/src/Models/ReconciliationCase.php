<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReconciliationCase extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'liberu_reconciliation_cases';

    protected $fillable = ['tenant_id', 'idempotency_key', 'name', 'status', 'metadata'];

    public function scopeForTenant($query, string|int $tenantId): void
    {
        $query->where($this->qualifyColumn('tenant_id'), (string) $tenantId);
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}

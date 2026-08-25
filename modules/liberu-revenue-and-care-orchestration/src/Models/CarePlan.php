<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CarePlan extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'liberu_care_plans';

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

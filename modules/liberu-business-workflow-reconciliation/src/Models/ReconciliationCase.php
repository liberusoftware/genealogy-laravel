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

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}

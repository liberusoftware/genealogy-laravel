<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GenealogyReport extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_reports';

    protected $fillable = ['name', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}

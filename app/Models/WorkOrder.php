<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Núcleo del sistema. El código (ST-2026-000001) se genera automáticamente
 * al crear la orden, con secuencial reiniciado cada año.
 */
class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'client_id', 'equipment_id', 'assigned_technician_id',
        'created_by', 'status', 'priority', 'reported_issue',
        'lat', 'lng', 'scheduled_at', 'started_at', 'finished_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $order) {
            if (empty($order->code)) {
                $order->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $year = now()->year;

        // Bloqueo a nivel de fila para evitar códigos duplicados con
        // técnicos creando órdenes simultáneamente desde el celular.
        return DB::transaction(function () use ($year) {
            $last = static::withTrashed()
                ->where('code', 'like', "ST-{$year}-%")
                ->orderByDesc('code')
                ->lockForUpdate()
                ->first();

            $nextNumber = $last
                ? ((int) substr($last->code, -6)) + 1
                : 1;

            return sprintf('ST-%d-%06d', $year, $nextNumber);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkOrderEvent::class)->orderBy('occurred_at');
    }

    public function technicalReport(): HasOne
    {
        return $this->hasOne(TechnicalReport::class);
    }

    public function materialsTotal(): float
    {
        return (float) $this->items()->where('type', 'material')->sum('subtotal');
    }

    public function laborTotal(): float
    {
        return (float) $this->items()->where('type', 'mano_de_obra')->sum('subtotal');
    }
}

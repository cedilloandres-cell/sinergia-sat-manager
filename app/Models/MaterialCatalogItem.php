<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialCatalogItem extends Model
{
    protected $table = 'material_catalog';

    protected $fillable = [
        'tenant_id', 'sku', 'name', 'category', 'cost_price',
        'sale_price', 'stock_quantity', 'min_stock_alert',
    ];

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }
}

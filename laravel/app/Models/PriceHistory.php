<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['platform_product_id', 'price'];

    public function platformProduct(): BelongsTo{
        return $this->belongsTo(PlatformProduct::class);
    }
}

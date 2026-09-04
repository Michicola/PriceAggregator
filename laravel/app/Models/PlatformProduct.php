<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformProduct extends Model
{
    protected $fillable = [
        'product_id',
        'platform_id',
        'url',
        'article_id',
        'current_price',
        'old_price'
    ];

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }

    public function platform(): BelongsTo{
        return $this->belongsTo(Platform::class);
    }

    public function priceHistories(): HasMany{
        return $this->hasMany(PriceHistory::class);
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}

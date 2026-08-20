<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['title', 'barcode'];

    public function platformProducts(): HasMany{
        return $this->hasMany(PlatformProduct::class);
    }
}

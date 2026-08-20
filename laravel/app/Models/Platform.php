<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    protected $fillable = ['name', 'base_url'];

    public function platformProducts(): HasMany{
        return $this->hasMany(PlatformProduct::class);
    }
}

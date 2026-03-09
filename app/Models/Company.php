<?php

namespace App\Models;

use App\Traits\HasVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasVersions;

    protected $fillable = [
        'name',
        'edrpou',
        'address',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(CompanyVersion::class);
    }
}

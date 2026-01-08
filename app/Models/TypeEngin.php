<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeEngin extends Model
{
    protected $table = 'type_engins';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'type',
    ];

    public function engins(): HasMany
    {
        return $this->hasMany(Engin::class, 'type_engin');
    }
}

<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use Auditable;

    protected static bool $autoAudit = true;

    protected $table = 'm_doctors';

    protected $primaryKey = 'pid';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'pid',
        'name_real',
        'poli',
        'sex',
        'spesialis',
        'url_image',
        'riwayat_pendidikan',
        'kursus',
        'last_update',
    ];

    protected $casts = [
        'last_update' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DoctorDetail::class, 'pid', 'pid');
    }
}
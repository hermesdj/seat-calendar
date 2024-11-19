<?php

namespace Seat\Kassie\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Eveapi\Models\Character\CharacterInfo;

class PapFleet extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'calendar_pap_fleets';

    protected $primaryKey = [
        'operation_id', 'fleet_commander_id',
    ];

    protected $fillable = [
        'operation_id', 'fleet_id', 'fleet_commander_id',
    ];

    public function commander(): BelongsTo
    {
        return $this->belongsTo(CharacterInfo::class, 'fleet_commander_id', 'id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }
}

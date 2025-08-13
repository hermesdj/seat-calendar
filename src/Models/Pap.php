<?php

/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 21/12/2017
 * Time: 11:24
 */

namespace Seat\Kassie\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Web\Models\User;

/**
 * Class Pap.
 */
class Pap extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'kassie_calendar_paps';

    /**
     * @var array
     */
    protected $primaryKey = [
        'operation_id', 'character_id',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'operation_id', 'character_id', 'ship_type_id', 'join_time', 'value',
    ];

    public function save(array $options = []): bool
    {
        logger()->debug('Saving Pap record with options', $options);
        $operation = Operation::find($this->getAttributeValue('operation_id'));

        if (is_null($this->getAttributeValue('value'))) {
            $this->setAttribute('value', 0);
        }

        if (!is_null($operation) && $operation->tags->count() > 0) {
            $this->setAttribute('value', $operation->tags->max('quantifier'));
        }

        if (array_key_exists('join_time', $this->attributes)) {
            $dt = carbon($this->getAttributeValue('join_time'));
            $this->setAttribute('week', $dt->weekOfMonth);
            $this->setAttribute('month', $dt->month);
            $this->setAttribute('year', $dt->year);
        }

        logger()->debug('Saving Pap record', $this->getAttributes());

        return parent::save($options);
    }

    public function character(): HasOne
    {
        return $this->hasOne(CharacterInfo::class, 'character_id', 'character_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, RefreshToken::class,
            'character_id', 'id', 'character_id', 'user_id')
            ->withDefault([
                'name' => trans('web::seat.unknown'),
            ]);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id')
            ->withDefault([
                'title' => trans('web::seat.unknown'),
            ]);
    }

    public function type(): HasOne
    {
        return $this->hasOne(InvType::class, 'typeID', 'ship_type_id')
            ->withDefault([
                'typeName' => trans('web::seat.unknown'),
            ]);
    }
}

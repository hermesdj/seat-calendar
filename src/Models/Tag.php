<?php

namespace Seat\Kassie\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Tag.
 *
 * @package Seat\Kassie\Calendar\Models
 */
class Tag extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'calendar_tags';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'bg_color',
        'text_color',
        'order',
        'quantifier',
        'analytics',
    ];

    /**
     * @return BelongsToMany
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'calendar_tag_operation');
    }
}

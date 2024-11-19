<?php

namespace Seat\Kassie\Calendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Seat\Notifications\Models\Integration;

/**
 * Class Tag.
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

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'calendar_tag_operation');
    }

    public function integrations(): BelongsToMany
    {
        return $this->belongsToMany(Integration::class, 'calendar_tag_integration');
    }
}

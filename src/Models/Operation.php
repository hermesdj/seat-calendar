<?php

namespace Seat\Kassie\Calendar\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use s9e\TextFormatter\Bundles\Forum as TextFormatter;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Seat\Notifications\Models\Integration;
use Seat\Web\Models\User;

/**
 * Class Operation.
 * @package Seat\Kassie\Calendar\Models
 */
class Operation extends Model
{
    use Notifiable;

    /**
     * @var string
     */
    protected $table = 'calendar_operations';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'start_at',
        'end_at',
        'importance',
        'integration_id',
        'description',
        'description_new',
        'staging_sys',
        'staging_sys_id',
        'staging_info',
        'is_cancelled',
        'fc',
        'fc_character_id',
        'role_name',
    ];

    /**
     * @var array
     */
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * @return HasOne
     */
    public function fleet_commander(): HasOne
    {
        return $this->hasOne(CharacterInfo::class, 'character_id', 'fc_character_id');
    }

    /**
     * @return HasMany
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'calendar_tag_operation');
    }

    /**
     * @return HasOne
     */
    public function staging(): HasOne
    {
        return $this->hasOne(MapDenormalize::class, 'itemID', 'staging_sys_id')
            ->withDefault();
    }

    /**
     * @return bool
     */
    public function getIsFleetCommanderAttribute(): bool
    {
        if ($this->fc_character_id == null)
            return false;

        return in_array($this->fc_character_id, auth()->user()->associatedCharacterIds());
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getDescriptionAttribute($value): mixed
    {
        return $value ?: $this->description_new;
    }

    /**
     * @param $value
     */
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description_new'] = $value;
    }

    /**
     * @return string
     */
    public function getParsedDescriptionAttribute(): string
    {
        $parser = TextFormatter::getParser();
        $parser->disablePlugin('Emoji');

        $xml = $parser->parse($this->description ?: $this->description_new);

        return TextFormatter::render($xml);
    }

    /**
     * @return string|null
     */
    public function getDurationAttribute(): ?string
    {
        if ($this->end_at)
            return $this->end_at->diffForHumans($this->start_at,
                [
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                    'options' => Carbon::ROUND,
                ]
            );

        return null;
    }

    /**
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_cancelled)
            return "cancelled";

        if ($this->start_at > Carbon::now('UTC'))
            return "incoming";

        if ($this->end_at > Carbon::now('UTC'))
            return "ongoing";

        return "faded";
    }

    /**
     * @return string
     */
    public function getStartsInAttribute(): string
    {
        return $this->start_at->diffForHumans(Carbon::now('UTC'),
            [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'options' => Carbon::ROUND,
            ]
        );
    }

    /**
     * @return string
     */
    public function getEndsInAttribute(): string
    {
        return $this->end_at->longRelativeToNowDiffForHumans(Carbon::now('UTC'),
            [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'options' => Carbon::ROUND,
            ]
        );
    }

    /**
     * @return string
     */
    public function getStartedAttribute(): string
    {
        return $this->start_at->longRelativeToNowDiffForHumans(Carbon::now('UTC'),
            [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'options' => Carbon::ROUND,
            ]
        );
    }

    /**
     * @param $user_id
     * @return string|null
     */
    public function getAttendeeStatus($user_id): ?string
    {
        $entry = $this->attendees->where('user_id', $user_id)->first();

        if ($entry != null)
            return $entry->status;

        return null;
    }

    /**
     * @return string
     */
    public function routeNotificationForSlack(): string
    {

        if (!is_null($this->integration()))
            return $this->integration->settings['url'];

        return '';
    }

    /**
     * @return BelongsTo
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'id');
    }

    /**
     * Return true if the user can see the operation
     *
     * @param User $user
     * @return bool
     */
    public function isUserGranted(User $user): bool
    {
        if (is_null($this->role_name))
            return true;

        return $user->roles->where('title', $this->role_name)->isNotEmpty() || auth()->user()->isAdmin();
    }
}

<?php

namespace RTippin\Messenger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use RTippin\Messenger\Contracts\MessengerProvider;
use RTippin\Messenger\Database\Factories\BotFactory;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Support\Helpers;
use RTippin\Messenger\Traits\ScopesProvider;
use RTippin\Messenger\Traits\Uuids;

/**
 * @property string $id
 * @property string $thread_id
 * @property string|int $owner_id
 * @property string $owner_type
 * @property string $name
 * @property string $avatar
 * @property bool $enabled
 * @property int $cooldown
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \RTippin\Messenger\Models\Thread $thread
 * @property-read \RTippin\Messenger\Models\BotAction[]|Collection $actions
 * @mixin Model|\Eloquent
 * @property-read Model|MessengerProvider $owner
 * @method static Builder|BotAction hasActionWithHandler(string $handler)
 */
class Bot extends Model implements MessengerProvider
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;
    use ScopesProvider;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bots';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * @return MorphTo|MessengerProvider
     */
    public function owner(): MorphTo
    {
        return $this->morphTo()->withDefault(function () {
            return Messenger::getGhostProvider();
        });
    }

    /**
     * @return BelongsTo|Thread
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * @return HasMany|BotAction|Collection
     */
    public function actions(): HasMany
    {
        return $this->hasMany(BotAction::class);
    }

    /**
     * Scope bots that have a specified handler.
     *
     * @param Builder $query
     * @param string $handler
     * @return Builder
     */
    public function scopeHasActionWithHandler(Builder $query, string $handler): Builder
    {
        return $query->whereHas('actions', fn (Builder $query) => $query->handler($handler));
    }

    /**
     * @return string|null
     */
    public function getProviderProfileRoute(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return strip_tags(ucwords($this->name));
    }

    /**
     * @return string
     */
    public function getProviderAvatarColumn(): string
    {
        return 'avatar';
    }

    /**
     * @return string
     */
    public function getProviderLastActiveColumn(): string
    {
        return 'updated_at';
    }

    /**
     * @return string
     */
    public function getStorageDisk(): string
    {
        return Messenger::getThreadStorage('disk');
    }

    /**
     * @return string
     */
    public function getStorageDirectory(): string
    {
        return Messenger::getThreadStorage('directory')."/$this->thread_id/bots/$this->id";
    }

    /**
     * @return string
     */
    public function getAvatarDirectory(): string
    {
        return "{$this->getStorageDirectory()}/avatar";
    }

    /**
     * @return string
     */
    public function getAvatarPath(): string
    {
        return "{$this->getAvatarDirectory()}/$this->avatar";
    }

    /**
     * @param string $size
     * @param bool $api
     * @return string|null
     */
    public function getProviderAvatarRoute(string $size = 'sm', bool $api = false): ?string
    {
        return Helpers::Route(($api ? 'api.' : '').'messenger.threads.bots.avatar.render',
            [
                'thread' => $this->thread_id,
                'bot' => $this->id,
                'size' => $size,
                'image' => $this->avatar ?: 'default.png',
            ]
        );
    }

    /**
     * @return int
     */
    public function getProviderOnlineStatus(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getProviderOnlineStatusVerbose(): string
    {
        return 'offline';
    }

    /**
     * Does the bot have an active cooldown?
     *
     * @return bool
     */
    public function hasCooldown(): bool
    {
        return Cache::has("bot:$this->id:cooldown");
    }

    /**
     * Set the bots cooldown.
     */
    public function startCooldown(): void
    {
        Cache::put("bot:$this->id:cooldown", true, now()->addSeconds($this->cooldown));
    }

    /**
     * Release the bots cooldown.
     */
    public function releaseCooldown(): void
    {
        Cache::forget("bot:$this->id:cooldown");
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return BotFactory::new();
    }
}

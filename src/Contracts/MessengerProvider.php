<?php

namespace RTippin\Messenger\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Contracts\MessengerProvider.
 *
 * @mixin Model|\Eloquent
 */
interface MessengerProvider
{
    /**
     * Get the provider settings and alias override, if set.
     *
     * @return array
     */
    public static function getProviderSettings(): array;

    /**
     * Format and return your provider name here.
     * ex: $this->first . ' ' . $this->last.
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * The column name your providers avatar is stored in the database as.
     *
     * @return string
     */
    public function getProviderAvatarColumn(): string;

    /**
     * The column name your provider has in the database that we will use to
     * show last active, and touch / update timestamp when using our online
     * heartbeat. This should be a timestamp column.
     *
     * @return string
     */
    public function getProviderLastActiveColumn(): string;

    /**
     * Get the route of the avatar for your provider. We will call this
     * from our resource classes using sm/md/lg .
     *
     * @param string $size
     * @return string|null
     */
    public function getProviderAvatarRoute(string $size = 'sm'): ?string;

    /**
     * If your provider has a route/slug for a profile page,
     * return that route here.
     *
     * @return string|null
     */
    public function getProviderProfileRoute(): ?string;

    /**
     * Returns online status of your provider.
     * 0 - offline, 1 - online, 2 - away.
     *
     * @return int
     */
    public function getProviderOnlineStatus(): int;

    /**
     * Verbose meaning of the online status number.
     *
     * @return string
     */
    public function getProviderOnlineStatusVerbose(): string;
}

<?php

namespace RTippin\Messenger\Contracts;

use RTippin\Messenger\Models\Messenger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Contracts\MessengerProvider
 *
 * @mixin Model
 */
interface MessengerProvider
{
    /**
     * @return Messenger
     */
    public function messenger();

    /**
     * @return Carbon|string|null
     */
    public function lastActiveDateTime();

    /**
     * @return string
     */
    public function name();

    /**
     * @param string $size
     * @return string|null
     */
    public function getAvatarRoute(string $size = 'sm');

    /**
     * @param bool $full
     * @return string
     */
    public function slug($full = false);

    /**
     * @return int
     */
    public function onlineStatus();

    /**
     * @return string
     */
    public function onlineStatusVerbose();
}
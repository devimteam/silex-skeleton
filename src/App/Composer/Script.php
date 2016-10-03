<?php

namespace App\Composer;

use Composer\Script\Event;

/**
 * Class Script.
 */
class Script
{
    /**
     * @param Event $event
     */
    public static function postInstall(Event $event)
    {
        self::setup();
    }

    /**
     */
    private static function setup()
    {
        self::mkDir('runtime');
        self::mkDir('runtime/logs');
        self::mkDir('runtime/cache');
        self::mkDir('runtime/cache/doctrine');
        self::mkDir('runtime/cache/doctrine/proxies');
    }

    /**
     * @param string $path
     * @param $mode
     */
    private static function mkDir(string $path, $mode = 0755)
    {
        if (!is_dir($path)) {
            mkdir($path, $mode, true);
        }
    }

    /**
     * @param Event $event
     */
    public static function postUpdate(Event $event)
    {
        self::setup();
    }
}

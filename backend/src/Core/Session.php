<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        if (!empty($_SESSION)) {
            return;
        }

        if (!headers_sent()) {
            @session_start();
        }
    }
}

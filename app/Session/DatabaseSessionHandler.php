<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler as IlluminateDatabaseSessionHandler;

/**
 * The database session handler, without the visitor's IP address.
 *
 * Laravel writes Request::ip() in the ip_address column of the sessions
 * table. Since the proxies are trusted (bootstrap/app.php), that is each
 * visitor's own address, which the privacy policy does not cover: the
 * column, nullable, stays empty. Registered in AppServiceProvider.
 */
class DatabaseSessionHandler extends IlluminateDatabaseSessionHandler
{
    /**
     * No address is stored
     */
    protected function ipAddress()
    {
        return null;
    }
}

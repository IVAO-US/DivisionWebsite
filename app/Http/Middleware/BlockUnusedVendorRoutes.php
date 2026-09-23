<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Answer 404 on the vendor routes the site does not use.
 *
 * MaryUI registers three server routes of its own, whatever components a
 * site renders, with nothing but `web` (plus `auth` for the upload), outside
 * the throttle groups of routes/web.php:
 *
 * - mary.upload stores the file any signed-in user sends - here, any IVAO
 *   member who logs in through the SSO - on the disk and in the folder named
 *   by the request, with no type or size check. Once `storage:link` has run,
 *   the public disk is served from /storage by the web server, same-origin
 *   and without the Content-Security-Policy of the app, so an uploaded SVG
 *   carrying a script would run in the site's origin;
 * - mary.spotlight resolves App\Support\Spotlight, which the site does not
 *   define, so every request ends in a 500;
 * - mary.toogle-sidebar writes to the session: one new session row per
 *   request without a cookie.
 *
 * The site has no editor or markdown upload, no spotlight and no collapsible
 * MaryUI sidebar. This middleware sits at the front of the `web` group, so
 * these paths are answered like any unknown URL, before a session is started
 * or a CSRF token checked.
 *
 * To use one of these features, protect its route before removing its name
 * below: the vendor upload trusts the disk and folder sent by the browser,
 * and <x-spotlight> can be given a `url` of the site's own instead.
 */
class BlockUnusedVendorRoutes
{
    /**
     * Names of the routes answered with 404
     */
    public const ROUTES = [
        'mary.upload',
        'mary.spotlight',
        'mary.toogle-sidebar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(...self::ROUTES)) {
            abort(404);
        }

        return $next($request);
    }
}

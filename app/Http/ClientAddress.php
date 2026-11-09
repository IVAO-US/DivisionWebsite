<?php

namespace App\Http;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * The address of the visitor, behind Cloudflare and the host's local proxy.
 *
 * bootstrap/app.php trusts these proxies for X-Forwarded-For only:
 * Request::ip() is then the right-most address of the chain that none of
 * them added, the one Cloudflare received the request from. A client that
 * reaches PHP directly cannot pick it: its own address is no trusted proxy,
 * so the header it sends is ignored.
 */
final class ClientAddress
{
    /**
     * The host's own addresses: loopback (IPv4, IPv6, IPv4-mapped) and the
     * private networks a local proxy may use
     */
    public const LOCAL = [
        '127.0.0.0/8',
        '::1',
        '::ffff:127.0.0.0/104',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        'fc00::/7',
    ];

    /**
     * Cloudflare's ranges, as published on https://www.cloudflare.com/ips/
     * (the list has not changed since April 2021). Visitors behind a range
     * missing here would share the counter of Cloudflare's address.
     */
    public const CLOUDFLARE = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * The proxies trusted for X-Forwarded-For (bootstrap/app.php)
     */
    public const PROXIES = [...self::LOCAL, ...self::CLOUDFLARE];

    /**
     * The visitor's address, or null when the chain names none.
     *
     * A request without X-Forwarded-For only shows the local proxy, whose
     * address would be one counter for every guest. Cloudflare's own
     * addresses (a Worker fetching the site) count like any other.
     */
    public static function of(Request $request): ?string
    {
        $address = $request->ip();

        return $address === null || IpUtils::checkIp($address, self::LOCAL) ? null : $address;
    }
}

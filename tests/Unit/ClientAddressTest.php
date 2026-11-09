<?php

use App\Http\ClientAddress;
use Illuminate\Http\Request;

/*
 * The visitor's address behind Cloudflare and the host's local proxy, with
 * the proxies trusted as bootstrap/app.php trusts them. Symfony counts the
 * documentation ranges as private: the visitors use public addresses.
 */

beforeEach(function () {
    Request::setTrustedProxies(ClientAddress::PROXIES, Request::HEADER_X_FORWARDED_FOR);
});

afterEach(function () {
    Request::setTrustedProxies([], -1);
});

/**
 * The address ClientAddress gives for a request from this peer and header
 */
function clientAddressOf(string $remoteAddress, ?string $forwardedFor = null): ?string
{
    $server = ['REMOTE_ADDR' => $remoteAddress];

    if ($forwardedFor !== null) {
        $server['HTTP_X_FORWARDED_FOR'] = $forwardedFor;
    }

    return ClientAddress::of(Request::create('/', server: $server));
}

test('the visitor seen by Cloudflare', function (string $remoteAddress, string $forwardedFor, string $visitor) {
    expect(clientAddressOf($remoteAddress, $forwardedFor))->toBe($visitor);
})->with([
    'IPv4' => ['127.0.0.1', '1.2.3.4, 173.245.48.10', '1.2.3.4'],
    'IPv6' => ['127.0.0.1', '2001:4860::8888, 2400:cb00::10', '2001:4860::8888'],
    'the IPv6 loopback as peer' => ['::1', '1.2.3.4, 173.245.48.10', '1.2.3.4'],
    'an IPv4-mapped loopback as peer' => ['::ffff:127.0.0.1', '1.2.3.4, 173.245.48.10', '1.2.3.4'],
    'an address forged on the left' => ['127.0.0.1', '5.6.7.8, 1.2.3.4, 173.245.48.10', '1.2.3.4'],
    'an entry that is no address' => ['127.0.0.1', 'nonsense, 1.2.3.4, 173.245.48.10', '1.2.3.4'],
]);

test('no address when the chain names no visitor', function (?string $forwardedFor) {
    expect(clientAddressOf('127.0.0.1', $forwardedFor))->toBeNull();
})->with([
    'no header' => [null],
    'a private address' => ['10.0.0.5'],
    'the IPv6 loopback' => ['::1'],
]);

test('a request from Cloudflare itself counts by that address', function () {
    // A Worker fetching the site: its requests come from Cloudflare's ranges
    expect(clientAddressOf('127.0.0.1', '2a06:98c0:3600::103, 173.245.48.10'))->toBe('2a06:98c0:3600::103');
});

test('a client that reaches PHP directly keeps its own address', function () {
    expect(clientAddressOf('9.9.9.9', '1.2.3.4'))->toBe('9.9.9.9');
});

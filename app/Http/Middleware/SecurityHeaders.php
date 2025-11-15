<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy - balance privacy and functionality
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy - disable unused browser features
        $response->headers->set('Permissions-Policy', implode(', ', [
            'geolocation=()',           // Disable geolocation
            'microphone=()',            // Disable microphone
            'camera=()',                // Disable camera
            'payment=()',               // Disable payment API
            'usb=()',                   // Disable USB
            'magnetometer=()',          // Disable magnetometer
            'gyroscope=()',             // Disable gyroscope
            'accelerometer=()',         // Disable accelerometer
        ]));

        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",  // Livewire needs unsafe-inline/eval
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com", // Tailwind + Google Fonts
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.gstatic.com",  // Inter font + Google Fonts
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // HTTP Strict Transport Security (HSTS) 
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Expect-CT (Certificate Transparency)
        $response->headers->set('Expect-CT', 'max-age=86400, enforce');

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        return $response;
    }
}

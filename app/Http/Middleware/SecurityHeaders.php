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

        // Disable the legacy XSS auditor: removed from modern browsers and a source
        // of vulnerabilities where it still exists (OWASP recommends 0)
        $response->headers->set('X-XSS-Protection', '0');

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
            "style-src 'self' 'unsafe-inline'",  // Tailwind; fonts are self-hosted, no third-party stylesheet
            "img-src 'self' data: https:",
            "font-src 'self' data:",  // Self-hosted Poppins and Nunito Sans (Vite build) and Dosis (error pages)
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

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        return $response;
    }
}

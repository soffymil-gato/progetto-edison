<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Area;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP security headers, including a nonce-based Content Security Policy.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Content-Security-Policy', $this->contentSecurityPolicy($nonce));

        // Reserved areas must never be indexed by search engines.
        if (Area::current() !== Area::Public) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        $self = "'self'";
        $scriptSrc = [$self, "'nonce-{$nonce}'"];
        $styleSrc = [$self, "'nonce-{$nonce}'"];
        $connectSrc = [$self];

        // Local development only: allow the Vite dev server (HMR).
        if (Vite::isRunningHot()) {
            $hot = rtrim((string) file_get_contents(public_path('hot')));
            $scriptSrc[] = $hot;
            $styleSrc = [$self, "'unsafe-inline'", $hot];
            $connectSrc[] = $hot;
            $connectSrc[] = (string) preg_replace('#^http#', 'ws', $hot);
        }

        $directives = [
            'default-src' => [$self],
            'script-src' => $scriptSrc,
            'style-src' => $styleSrc,
            // Inline style attributes (positioning by UI primitives) are allowed;
            // injected <style> elements still need the nonce.
            'style-src-attr' => ["'unsafe-inline'"],
            'img-src' => [$self, 'data:'],
            'font-src' => [$self],
            'connect-src' => $connectSrc,
            'form-action' => [$self],
            'frame-ancestors' => ["'none'"],
            'base-uri' => [$self],
            'object-src' => ["'none'"],
        ];

        return implode('; ', array_map(
            static fn (string $name, array $values): string => $name.' '.implode(' ', $values),
            array_keys($directives),
            $directives,
        ));
    }
}

<?php

namespace App\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use TypeError;

/**
 * A forged Livewire request: Livewire, or the container, refused the
 * updates or calls the request carries before any code of the site ran.
 *
 * Livewire's own JavaScript never sends such a request: a deep write into a
 * scalar, an unknown or locked property, a method that is no action, an
 * event nobody listens to, a parameter missing or of the wrong type.
 * Livewire 4.4.6 answers most of them with a 500 and logs them;
 * bootstrap/app.php answers them with the 419 Livewire gives a corrupt
 * snapshot, and logs nothing: only their sender sees them.
 *
 * An error raised by the site's own code, even on a forged parameter, or
 * while rendering, is a bug: still a 500, still logged. So is everything in
 * debug mode.
 */
final class ForgedLivewireRequest
{
    /**
     * The steps of HandleComponents that apply the request's updates and
     * calls
     */
    private const PAYLOAD_STEPS = [
        'updateProperties',
        'updateProperty',
        'recursivelySetValue',
        'setComponentPropertyAwareOfTypes',
        'callMethods',
    ];

    public static function refused(Throwable $e): bool
    {
        if (config('app.debug') || $e instanceof HttpExceptionInterface || ! request()->routeIs('*livewire.update')) {
            return false;
        }

        // The container also fails on an action typed with an unbound interface: a bug
        if ($e instanceof BindingResolutionException && ! str_starts_with($e->getMessage(), 'Unable to resolve dependency [')) {
            return false;
        }

        // A TypeError raised as a function is called: its body never ran, its caller did
        $sites = $e instanceof TypeError && str_contains($e->getMessage(), ', called in ') ? [] : [$e->getFile()];

        foreach ($e->getTrace() as $frame) {
            if (($frame['class'] ?? null) === HandleComponents::class) {
                return in_array($frame['function'], self::PAYLOAD_STEPS, true) && self::allInVendor($sites);
            }

            if (isset($frame['file'])) {
                $sites[] = $frame['file'];
            }
        }

        return false;
    }

    /**
     * Whether all these files belong to the dependencies, none to the site
     */
    private static function allInVendor(array $files): bool
    {
        $vendor = base_path('vendor').DIRECTORY_SEPARATOR;

        foreach ($files as $file) {
            if (! str_starts_with($file, $vendor)) {
                return false;
            }
        }

        return true;
    }
}

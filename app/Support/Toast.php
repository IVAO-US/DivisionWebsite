<?php

namespace App\Support;

use Mary\Traits\Toast as MaryToast;

/**
 * MaryUI's Toast trait, with the icon checked before MaryUI compiles it.
 *
 * MaryUI renders a toast's icon with
 * Blade::render("<x-mary-icon name='".$icon."' />"): an icon that is no
 * icon name is a Blade template run on the server. AppServiceProvider
 * refuses the browser's direct calls of the toast methods, but a component
 * relaying a browser value to an icon, from a listener or an action, calls
 * them itself, out of the guard's sight. Any icon that is not a plain name
 * (letters, digits, dots, dashes, underscores) is replaced by MaryUI's
 * default one. success(), warning(), error() and info() all end in
 * toast(), so they pass here too.
 *
 * Use this trait, never MaryUI's. A component that takes an icon from the
 * browser still checks it against a list of its own: this is a safety net.
 */
trait Toast
{
    use MaryToast {
        toast as protected maryToast;
    }

    public function toast(
        string $type,
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $icon)) {
            $icon = 'o-information-circle';
        }

        return $this->maryToast($type, $title, $description, $position, $icon, $css, $timeout, $redirectTo, $noProgress, $progressClass);
    }
}

<?php

use Illuminate\Contracts\Debug\ExceptionHandler;

/*
 * The log is one file (LOG_STACK=single): a client repeating a request that
 * fails would fill it. The same error is logged at most ten times a minute
 * for each place it is thrown from (bootstrap/app.php).
 */

/**
 * An error thrown from one and the same place
 */
function sameError(): RuntimeException
{
    return new RuntimeException('The same bug');
}

test('the same error is logged at most ten times a minute', function () {
    $handler = app(ExceptionHandler::class);

    $logged = collect(range(1, 11))->filter(fn () => $handler->shouldReport(sameError()));

    expect($logged)->toHaveCount(10);
});

test('an error thrown from elsewhere is still logged', function () {
    $handler = app(ExceptionHandler::class);

    foreach (range(1, 10) as $time) {
        $handler->shouldReport(sameError());
    }

    expect($handler->shouldReport(new RuntimeException('Another bug')))->toBeTrue();
});

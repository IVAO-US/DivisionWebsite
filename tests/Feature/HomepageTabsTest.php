<?php

/*
 * MaryUI derives a tab bar's id from its attributes, and each tab
 * teleports its label into "#<id>-labels". The homepage's two mobile tab
 * bars (Division Highlights, Flight Operations) had the same attributes,
 * hence the same id: every label landed in the first bar, which then held
 * four tabs, two of them active, wider than the screen.
 */

test('each tab bar of the homepage keeps its own labels', function () {
    $html = $this->withoutVite()->get('/')->assertOk()->getContent();

    preg_match_all('/id="([^"]+)-labels"/', $html, $matches);

    expect($matches[1])->toHaveCount(2)
        ->and(array_unique($matches[1]))->toHaveCount(2);
});

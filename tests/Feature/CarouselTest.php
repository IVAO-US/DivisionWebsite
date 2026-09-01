<?php

use Livewire\Livewire;

/*
 * The carousel's polling element renders even when it has no items, so
 * `autoAdvance` is called on an empty carousel every few seconds. Guard
 * against the modulo by zero that used to make those requests fail.
 */

test('auto advance does nothing on an empty carousel', function () {
    Livewire::test('carousel', ['items' => []])
        ->set('lastInteractionTime', time() - 100)
        ->call('autoAdvance')
        ->assertSet('currentIndex', 0);
});

test('auto advance cycles through the items', function () {
    Livewire::test('carousel', ['items' => [['title' => 'A'], ['title' => 'B']]])
        ->set('lastInteractionTime', time() - 100)
        ->call('autoAdvance')
        ->assertSet('currentIndex', 1)
        ->set('lastInteractionTime', time() - 100)
        ->call('autoAdvance')
        ->assertSet('currentIndex', 0);
});

test('manual navigation does nothing on an empty carousel', function () {
    Livewire::test('carousel', ['items' => []])
        ->call('next')
        ->assertSet('currentIndex', 0)
        ->call('previous')
        ->assertSet('currentIndex', 0);
});

test('manual navigation wraps around the items', function () {
    Livewire::test('carousel', ['items' => [['title' => 'A'], ['title' => 'B']]])
        ->call('previous')
        ->assertSet('currentIndex', 1)
        ->call('next')
        ->assertSet('currentIndex', 0);
});

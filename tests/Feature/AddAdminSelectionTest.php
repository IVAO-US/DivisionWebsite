<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

/*
 * The member search of the add-admin form (users-list-table) sends the
 * member picked, or the VID typed, to the manage page. It named the page
 * without its namespace: Livewire found no such component and the request
 * failed, so a pick never reached the form.
 */

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->actingAs(createAdmin(['admins_edit_permissions']));
});

test('picking a member sends it to the manage page', function () {
    Livewire::test('users-list-table')
        ->call('selectUser', 1234567, 'Jane Doe')
        ->assertDispatchedTo('pages::protected.admin.manage', 'user-selected', vid: 1234567, name: 'Jane Doe');
});

test('a VID typed in the search goes to the manual entry', function () {
    Livewire::test('users-list-table', ['search' => '1234567'])
        ->call('transferVidToManualEntry')
        ->assertDispatchedTo('pages::protected.admin.manage', 'vid-transfer', vid: '1234567');
});

test('the manage page fills its form with the member picked', function () {
    Livewire::test('pages::protected.admin.manage')
        ->dispatch('user-selected', vid: 1234567, name: 'Jane Doe')
        ->assertSet('selectedVid', 1234567)
        ->assertSet('selectedName', 'Jane Doe')
        ->assertSet('isManualEntry', false);
});

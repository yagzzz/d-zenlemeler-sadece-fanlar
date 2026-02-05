<?php

use function Pest\Laravel\get;

beforeEach(function () {
    config()->set('features.flags.ui', true);
    config()->set('features.flags.ui_polish', true);
});

it('renders data-ui-enabled="1" when ui flag is true', function () {
    get('/')
        ->assertOk()
        ->assertSee('data-ui-enabled="1"', false);
});

it('renders data-ui-enabled="0" when ui flag is false', function () {
    config()->set('features.flags.ui', false);

    get('/')
        ->assertOk()
        ->assertSee('data-ui-enabled="0"', false);
});

it('renders data-ui-polish-enabled="1" when ui_polish flag is true', function () {
    get('/')
        ->assertOk()
        ->assertSee('data-ui-polish-enabled="1"', false);
});

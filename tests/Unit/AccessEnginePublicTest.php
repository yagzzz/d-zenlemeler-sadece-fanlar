<?php

use App\Services\AccessEngine\AccessRequest;
use App\Services\AccessEngine\DefaultAccessEngine;

uses(Tests\TestCase::class);

it('grants access for public visibility', function () {
    config()->set('features.flags.access_engine', true);

    $engine = new DefaultAccessEngine;
    $decision = $engine->decide(new AccessRequest(null, 'public'));

    expect($decision->granted)->toBeTrue();
    expect($decision->reason)->toBeNull();
});

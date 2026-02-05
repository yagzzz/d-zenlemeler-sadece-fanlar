<?php

use App\Models\Content;

it('detects public visibility', function () {
    $content = new Content(['visibility' => 'public']);

    expect($content->isPublic())->toBeTrue();
    expect($content->isRegisteredOnly())->toBeFalse();
    expect($content->isSubscriberOnly())->toBeFalse();
    expect($content->isPpv())->toBeFalse();
});

it('detects registered_only visibility', function () {
    $content = new Content(['visibility' => 'registered_only']);

    expect($content->isPublic())->toBeFalse();
    expect($content->isRegisteredOnly())->toBeTrue();
    expect($content->isSubscriberOnly())->toBeFalse();
    expect($content->isPpv())->toBeFalse();
});

it('detects subscriber_only visibility', function () {
    $content = new Content(['visibility' => 'subscriber_only']);

    expect($content->isPublic())->toBeFalse();
    expect($content->isRegisteredOnly())->toBeFalse();
    expect($content->isSubscriberOnly())->toBeTrue();
    expect($content->isPpv())->toBeFalse();
});

it('detects ppv visibility', function () {
    $content = new Content(['visibility' => 'ppv']);

    expect($content->isPublic())->toBeFalse();
    expect($content->isRegisteredOnly())->toBeFalse();
    expect($content->isSubscriberOnly())->toBeFalse();
    expect($content->isPpv())->toBeTrue();
});

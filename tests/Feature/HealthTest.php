<?php

test('health endpoint returns ok', function () {
    $this->get('/health')
        ->assertOk()
        ->assertJson(['status' => 'ok']);
});

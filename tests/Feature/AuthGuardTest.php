<?php

test('protected route redirects guests', function () {
    $this->get('/protected')
        ->assertRedirect('/login');
});

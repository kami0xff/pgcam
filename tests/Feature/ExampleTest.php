<?php

test('health check returns ok', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
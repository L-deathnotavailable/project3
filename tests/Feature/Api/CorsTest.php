<?php

it('autorise les requêtes provenant du front React configuré', function () {
    $origin = config('cors.allowed_origins.0');

    $this->withHeaders([
        'Origin' => $origin,
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type',
    ])->options('/api/v1/login')
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', $origin);
});

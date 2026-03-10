<?php

declare(strict_types=1);

test('legacy analytics proxy endpoints are disabled', function (): void {
    $this->get('/va/script.js')->assertNotFound();
    $this->postJson('/va/api/send', ['type' => 'event'])->assertNotFound();
});

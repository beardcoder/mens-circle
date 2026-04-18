<?php

declare(strict_types=1);

test('breathing page is reachable and renders the interactive app', function (): void {
    $response = $this->get(route('breathing.show'));

    $response
        ->assertOk()
        ->assertSee('Atemübung', false)
        ->assertSee('breathingApp', false)
        ->assertSee('data-element=start', false);
});

test('breathing page is linked from the layout navigation', function (): void {
    $response = $this->get(route('breathing.show'));

    $response
        ->assertOk()
        ->assertSee(route('breathing.show'), false);
});

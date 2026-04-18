<?php

declare(strict_types=1);

test('breathing page is accessible', function (): void {
    $this->withoutVite();

    $response = $this->get(route('breathing'));

    $response->assertSuccessful();
    $response->assertSee('Interaktive Atemreise');
    $response->assertSee(route('breathing'), false);
    $response->assertSee('Atmung');
    $response->assertSee('data-breathing-start', false);
});

test('breathing link is present in site navigation', function (): void {
    $this->withoutVite();

    $response = $this->get(route('testimonial.form'));

    $response->assertSuccessful();
    $response->assertSee(route('breathing'), false);
    $response->assertSee('Atmung');
});

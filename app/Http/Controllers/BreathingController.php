<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class BreathingController
{
    public function show(): View
    {
        return view('breathing');
    }
}

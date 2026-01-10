<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Newsletter;

use Filament\Clusters\Cluster;

class NewsletterCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?int $navigationSort = 50;
}

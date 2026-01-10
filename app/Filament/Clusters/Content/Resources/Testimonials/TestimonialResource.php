<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Content\Resources\Testimonials;

use App\Filament\Clusters\Content\ContentCluster;
use App\Filament\Clusters\Content\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Clusters\Content\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Clusters\Content\Resources\Testimonials\Pages\ListTestimonials;
use App\Filament\Clusters\Content\Resources\Testimonials\Schemas\TestimonialForm;
use App\Filament\Clusters\Content\Resources\Testimonials\Tables\TestimonialsTable;
use App\Models\Testimonial;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $cluster = ContentCluster::class;

    protected static ?string $modelLabel = 'Erfahrungsbericht';

    protected static ?string $pluralModelLabel = 'Erfahrungsberichte';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return TestimonialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestimonialsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

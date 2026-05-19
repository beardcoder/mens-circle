@extends ('layouts.app')

@section ('title', $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section ('meta_title', $page->meta['meta_title'] ?? $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section ('meta_description', $page->meta['meta_description'] ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')
@section ('meta_keywords', $page->meta['meta_keywords'] ?? 'Männerkreis, Niederbayern, Männergruppe, persönliches Wachstum, Gemeinschaft, Männer')

@section ('og_title', $page->meta['og_title'] ?? $page->title ?? 'Männerkreis Niederbayern/ Straubing')
@section ('og_description', $page->meta['og_description'] ?? $page->meta['meta_description'] ?? 'Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.')
@if (!empty($page->meta['og_image']))
  @section ('og_image', asset('storage/' . $page->meta['og_image']))
@endif

@section ('content')
  @if ($page->contentBlocks->isNotEmpty())
    <x-page-content :page="$page" :testimonials="$testimonials ?? null" />
  @endif
@endsection

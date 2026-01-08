@props([
    'items' => [],
])

@if(count($items) > 0)
@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($items as $index => $item)
        {
            "@@type": "ListItem",
            "position": {{ $index + 1 }},
            "name": "{{ $item['name'] }}",
            "item": "{{ $item['url'] }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endpush
@endif

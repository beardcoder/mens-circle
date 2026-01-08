@props([
    'items' => [],
])

@if(count($items) > 0)
@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($items as $item)
        @if(!empty($item['question']) && !empty($item['answer']))
        {
            "@@type": "Question",
            "name": "{{ e($item['question']) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ e(strip_tags($item['answer'])) }}"
            }
        }@if(!$loop->last),@endif
        @endif
        @endforeach
    ]
}
</script>
@endpush
@endif

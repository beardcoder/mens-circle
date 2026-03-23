@props(['schema'])

@push('structured_data')
    {!! $schema->toScript() !!}
@endpush

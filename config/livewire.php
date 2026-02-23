<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payload Maximum Nesting Depth
    |--------------------------------------------------------------------------
    |
    | Livewire serializes component state as nested arrays. The RichEditor
    | (TipTap) produces deeply nested JSON (e.g. marks.attrs inside content
    | nodes) that can exceed the default limit of 10. Raised to 20 to
    | accommodate complex rich-text payloads without hitting this limit.
    |
    */

    'payload' => [
        'max_nesting_depth' => 20,
    ],

];

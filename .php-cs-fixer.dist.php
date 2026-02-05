<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/packages/mens_circle/Classes')
    ->in(__DIR__ . '/packages/fluid_forms/Classes')
    ->exclude('cache')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,        // Latest PER Coding Style
        '@PER-CS:risky' => true,

        // PHP 8.4+ Modern Features
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,

        // Array & Collection
        'array_syntax' => ['syntax' => 'short'],

        // Imports
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],

        // Strings - Prefer interpolation style
        'single_quote' => false, // Allow double quotes for string interpolation
        'string_implicit_backslashes' => false, // Allow modern string syntax

        // Closures & Arrows - Modern fn() spacing
        'lambda_not_used_import' => true,
        'function_declaration' => [
            'closure_function_spacing' => 'one', // fn () not fn()
        ],

        // Trailing commas
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        // Whitespace & Formatting
        'no_extra_blank_lines' => [
            'tokens' => [
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],

        // Native Functions
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],

        // PHPDoc
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,

        // Return Types
        'return_type_declaration' => ['space_before' => 'none'],

        // Visibility
        'visibility_required' => ['elements' => ['property', 'method', 'const']],

        // Match Expressions (PHP 8+)
        'control_structure_braces' => true,
        'control_structure_continuation_position' => ['position' => 'same_line'],

        // Don't override @PER-CS namespace rule
        'blank_lines_before_namespace' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');

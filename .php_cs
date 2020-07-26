D:\xampp_new\htdocs\opensourcewebsite-org\.php_cs<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:2.16.3|configurator
 * you can change this configuration by importing this file.
 */
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        'backtick_to_shell_exec' => true,
        'constant_case' => true,
        'elseif' => true,
        'indentation_type' => true,
        'no_closing_tag' => true,
        'blank_line_after_namespace' => true,
        'class_definition' => true,
        'single_line_after_imports' => true,
        'no_trailing_whitespace' => true,
        'switch_case_space' => true,
        'braces' => true,
        'no_trailing_whitespace_in_comment' => true,
        'function_declaration' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'concat_space' => ['spacing'=>'none'],
        'function_typehint_space' => true,
        'hash_to_slash_comment' => true,
        'method_argument_space' => ['after_heredoc'=>true,'keep_multiple_spaces_after_comma'=>true],
        'no_leading_namespace_whitespace' => true,
        'no_whitespace_before_comma_in_array' => true,
        'not_operator_with_space' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'ordered_interfaces' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'compact_nullable_typehint' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => true,
        'no_empty_statement' => true,
        'binary_operator_spaces' => true,
        'encoding' => true,
        'full_opening_tag' => true,
        'line_ending' => true,
        'lowercase_keywords' => true,
        'no_break_comment' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_inside_parenthesis' => true,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'switch_case_semicolon_to_colon' => true,
        'visibility_required' => true,
        'whitespace_after_comma_in_array' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax'=>'short'],
        'method_chaining_indentation' => true,
        'class_attributes_separation' => ['method'],
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__)
    )
;

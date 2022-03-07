<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->exclude('tests/Fixtures')
    ->in(__DIR__)
    ->append([
        __DIR__.'/dev-tools/doc.php',
        // __DIR__.'/php-cs-fixer', disabled, as we want to be able to run bootstrap file even on lower PHP version, to show nice message
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP81Migration' => true,
        '@PHP80Migration:risky' => true,
        'heredoc_indentation' => false,

        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
        'modernize_strpos' => true, // needs PHP 8+ or polyfill
        '@PSR2' => true,
        'trailing_comma_in_multiline' => true,
        '@PHP70Migration' => true,
        'single_quote' => true,
        'single_line_comment_style' => false,
        'single_blank_line_before_namespace' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
                'do',
                'for',
                'foreach',
                'if',
                'switch',
                'try',
            ],
        ],
        'short_scalar_cast' => true,
        'return_type_declaration' => true,
        'protected_to_private' => true,
        'phpdoc_scalar' => true,
        'phpdoc_no_access' => true,
        'object_operator_without_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_useless_return' => true,
        'no_useless_else' => true,
        'no_unused_imports' => true,
        'no_unneeded_final_method' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_leading_namespace_whitespace' => true,
        'no_leading_import_slash' => true,
        'no_empty_statement' => true,
        'no_empty_phpdoc' => true,
        'no_empty_comment' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_alias_functions' => true,
        'no_homoglyph_names' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_unset_on_property' => true,
        'no_extra_blank_lines' => [ //https://cs.symfony.com/doc/rules/whitespace/no_extra_blank_lines.html
            'tokens' => [
                'break',
                'case',
                'continue',
                'default',
                'return',
            ]
        ],
        'native_function_casing' => true,
        'modernize_types_casting' => true,
        'magic_method_casing' => true,
        'magic_constant_casing' => true,
        'lowercase_static_reference' => true,
        'lowercase_cast' => true,
        'logical_operators' => true,
        'list_syntax' => ['syntax' => 'short'],
        'function_typehint_space' => true,
        'fully_qualified_strict_types' => true,
        'fopen_flags' => true,
        'dir_constant' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'concat_space' => ['spacing' => 'one'],
        'compact_nullable_typehint' => true,
        'combine_nested_dirname' => true,
        'combine_consecutive_unsets' => true,
        'combine_consecutive_issets' => true,
        'class_attributes_separation' => true,
        'cast_spaces' => ['space' => 'none'],
        'binary_operator_spaces' => true,
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'pow_to_exponentiation' => true,
        'random_api_migration' => true,
        'set_type_to_cast' => true,
        'declare_strict_types' => true, // declare(strict_types=1);
        'linebreak_after_opening_tag' => true, // Blank
        'blank_line_after_opening_tag' => false,
        'ereg_to_preg' => true,
        'error_suppression' => true,
        'final_internal_class' => true,
        'fopen_flag_order' => true,
        'function_to_constant' => true,
        'implode_call' => true,
        'native_constant_invocation' => true,
        'native_function_invocation' => false,
        //'braces' => [
        // 'allow_single_line_closure' => true,
        // 'position_after_functions_and_oop_constructs' => 'same'
        //],
        'yoda_style' => [ // https://cs.symfony.com/doc/rules/control_structure/yoda_style.html
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_imports' => false,
        'non_printable_character' => true,
        'psr_autoloading' => true,
        'strict_comparison' => true,
        'string_line_ending' => true,
        'string_length_to_empty' => false,
        'ordered_class_elements' => false,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_summary' => false,
        'semicolon_after_instruction' => true,
        'multiline_whitespace_before_semicolons' => false,
        'phpdoc_to_comment' => false,
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        'comment_to_phpdoc' => true,
        'phpdoc_align'=> false,
        'no_alias_language_construct_call' => false, // This rules change die to exit
        'void_return' => false,
        'mb_str_functions' => false,
    ])
    ->setFinder($finder)
;

// special handling of fabbot.io service if it's using too old PHP CS Fixer version
if (false !== getenv('FABBOT_IO')) {
    try {
        PhpCsFixer\FixerFactory::create()
            ->registerBuiltInFixers()
            ->registerCustomFixers($config->getCustomFixers())
            ->useRuleSet(new PhpCsFixer\RuleSet($config->getRules()))
        ;
    } catch (PhpCsFixer\ConfigurationException\InvalidConfigurationException $e) {
        $config->setRules([]);
    } catch (UnexpectedValueException $e) {
        $config->setRules([]);
    } catch (InvalidArgumentException $e) {
        $config->setRules([]);
    }
}

return $config;

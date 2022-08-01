<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'method_chaining_indentation' => true,
        'align_multiline_comment' => true,
        'phpdoc_to_comment' => ['ignored_tags' => ['var']],
    ])
    ->setIndent("    ")
    ->setLineEnding("\n")
    ->setFinder($finder)
;

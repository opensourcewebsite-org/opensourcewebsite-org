<?php

# https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/usage.rst
# https://mlocati.github.io/php-cs-fixer-configurator/#version:3.16

declare(strict_types=1);

$config = new PhpCsFixer\Config();

/*
 * Available rules: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
 * Available sets: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
 */

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true, // PSR12 coding style: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/PSR12.rst
        '@PSR12:risky' => true, // PSR12Risky coding style: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/PSR12Risky.rst
        '@DoctrineAnnotation' => true, // Format Doctrine annotations: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/ruleSets/DoctrineAnnotation.rst
        'single_space_around_construct' => true, // Format Doctrine annotations: https://cs.symfony.com/doc/rules/language_construct/single_space_around_construct.html
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    );

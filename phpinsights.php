<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;

return [
    'preset' => 'default',
    'exclude' => [
    ],
    'add' => [
    ],
    'remove' => [
        DisallowMixedTypeHintSniff::class,
        ParameterTypeHintSniff::class,
        PropertyTypeHintSniff::class,
        ReturnTypeHintSniff::class,
        ForbiddenNormalClasses::class,
        SuperfluousInterfaceNamingSniff::class,
        SpaceAfterNotSniff::class,
        ExplicitStringVariableFixer::class,
        DisallowArrayTypeHintSyntaxSniff::class,
    ],
    'config' => [
        LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 120,
        ],
        DocCommentSpacingSniff::class => [
            'linesCountBetweenDifferentAnnotationsTypes' => 0,
        ],
    ],
];

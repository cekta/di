<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff;

return [
    'preset' => 'default',
    'exclude' => [
    ],
    'add' => [
    ],
    'remove' => [
        DisallowMixedTypeHintSniff::class,
        TypeHintDeclarationSniff::class,
        ForbiddenNormalClasses::class,
        SuperfluousInterfaceNamingSniff::class,
        SpaceAfterNotSniff::class,
        ExplicitStringVariableFixer::class,
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

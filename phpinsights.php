<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff;
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
    ],
    'config' => [
        LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 0,
            'ignoreComments' => false,
        ],
        DocCommentSpacingSniff::class => [
            'linesCountBeforeFirstContent' => 0,
            'linesCountBetweenDescriptionAndAnnotations' => 1,
            'linesCountBetweenDifferentAnnotationsTypes' => 0,
            'linesCountBetweenAnnotationsGroups' => 0,
            'linesCountAfterLastContent' => 0,
            'annotationsGroups' => [],
        ],
        CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
    ],
];

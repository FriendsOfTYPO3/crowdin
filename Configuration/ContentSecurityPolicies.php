<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries([
    // Provide declarations for the backend
    Scope::backend(),

    new MutationCollection(
        new Mutation(
            MutationMode::Append,
            Directive::ScriptSrc,
            new UriValue('https://cdn.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::ImgSrc,
            new UriValue('https://cdn.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::ImgSrc,
            new UriValue('https://crowdin-static.downloads.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::ImgSrc,
            new UriValue('https://crowdin-static.cf-downloads.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::StyleSrc,
            new UriValue('https://cdn.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::FrameSrc,
            new UriValue('https://crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::FrameSrc,
            new UriValue('https://accounts.crowdin.com')
        ),
        new Mutation(
            MutationMode::Append,
            Directive::StyleSrc,
            new UriValue('https://fonts.googleapis.com')
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::FontSrc,
            new UriValue('https://fonts.gstatic.com')
        )
    )
]);

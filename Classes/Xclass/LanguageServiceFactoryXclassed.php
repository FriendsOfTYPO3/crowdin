<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Xclass;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageServiceFactoryXclassed extends LanguageServiceFactory
{
    /**
     * Factory method to create a language service object.
     *
     * @param Locale|string $locale the locale
     */
    public function create(Locale|string $locale): LanguageService
    {
        $obj = new LanguageServiceXclassed($this->locales, $this->localizationFactory, $this->runtimeCache);
        $obj->init($locale instanceof Locale ? $locale : $this->locales->createLocale($locale));

        return $obj;
    }

    public function createFromUserPreferences(?AbstractUserAuthentication $user): LanguageService
    {
        if ($user && ($user->user['lang'] ?? false)) {
            return $this->create($this->locales->createLocale($user->user['lang']));
        }

        return $this->create('en');
    }

    public function createFromSiteLanguage(SiteLanguage $language): LanguageService
    {
        // createLocale from a string takes care of resolving the automatic dependencies of e.g. "de_AT" to also check for "de"
        // and also validates if TYPO3 supports the original language (at least in TYPO3 v12, there is a fixed list of
        // allowed language keys)
        $languageService = $this->create((string) $language->getLocale() ?: $language->getTypo3Language());
        // Always disable debugging for frontend
        // @deprecated since TYPO3 v12.4. will be removed in TYPO3 v13.0. Remove together with code in LanguageService
        $languageService->debugKey = false;

        return $languageService;
    }
}

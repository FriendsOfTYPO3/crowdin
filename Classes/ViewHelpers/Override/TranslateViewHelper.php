<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\ViewHelpers\Override;

use FriendsOfTYPO3\Crowdin\Traits\ConfigurationOptionsTrait;
use FriendsOfTYPO3\Crowdin\UserConfiguration;
use FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceXclassed;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class TranslateViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    use ConfigurationOptionsTrait;

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'Translation Key');
        $this->registerArgument('id', 'string', 'Translation ID. Same as key.');
        $this->registerArgument('default', 'string', 'If the given locallang key could not be found, this value is used. If this argument is not set, child nodes will be used to render the default');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string');
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('languageKey', 'string', 'Language key ("da" for example) or "default" to use. If empty, use current language. Ignored in non-extbase context.');
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist. Ignored in non-extbase context.');
    }
    /** @var UserConfiguration */
    protected static $userConfiguration;

    /**
     * Return array element by key.
     *
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @throws Exception
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $key = $arguments['key'];
        $id = $arguments['id'];
        $default = $arguments['default'];
        $extensionName = $arguments['extensionName'];
        $translateArguments = $arguments['arguments'];

        // Use key if id is empty.
        if ($id === null) {
            $id = $key;
        }

        if ((string)$id === '') {
            throw new Exception('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = $renderingContext->getRequest();

        if (!$request instanceof ExtbaseRequestInterface) {
            // Straight resolving via core LanguageService in non-extbase context
            if (!str_starts_with($id, 'LLL:EXT:') && empty($default)) {
                // Resolve "short key" without LLL:EXT: syntax given, if an extension name is given.
                // @todo: We could consider to deprecate this case. It is mostly implemented for a more
                //        smooth transition when (backend) controllers no longer feed an extbase request.
                if (empty($extensionName)) {
                    throw new \RuntimeException(
                        'ViewHelper f:translate in non-extbase context needs attribute "extensionName" to resolve'
                        . ' key="' . $id . '" without path. Either set attribute "extensionName" together with the short'
                        . ' key "yourKey" to result in a lookup "LLL:EXT:your_extension/Resources/Private/Language/locallang.xlf:yourKey",'
                        . ' or (better) use a full LLL reference like key="LLL:EXT:your_extension/Resources/Private/Language/yourFile.xlf:yourKey"',
                        1639828178
                    );
                }
                $id = 'LLL:EXT:' . GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName) . '/Resources/Private/Language/locallang.xlf:' . $id;
            }
            $value = self::getLanguageService($request)->sL($id);
            if (empty($value) || (!str_starts_with($id, 'LLL:EXT:') && $value === $id)) {
                // In case $value is empty (LLL: could not be resolved) or $value
                // is the same as $id and is no "LLL:", fall back to the default.
                $value = $default;
            }
            if (!empty($translateArguments)) {
                $value = vsprintf($value, $translateArguments);
            }
            return $value;
        }

        /** @var ExtbaseRequestInterface $request */
        $controllerExtensionName = $request->getControllerExtensionName() ?? '';
        $extensionName = $extensionName ?? GeneralUtility::camelCaseToLowerCaseUnderscored($controllerExtensionName);
        try {
            // Trigger full extbase magic: "<f:translate key="key1" />" will look up
            // "LLL:EXT:current_extension/Resources/Private/Language/locallang.xlf:key1" AND
            // overloads from _LOCAL_LANG extbase TypoScript settings if specified.
            // Note this triggers TypoScript parsing via extbase ConfigurationManager
            // and should be avoided in backend context!
            $value = static::translate($id, $extensionName, $translateArguments, $arguments['languageKey'], $arguments['alternativeLanguageKeys'] ?? []);
        } catch (\InvalidArgumentException $e) {
            // @todo: Switch to more specific Exceptions here - for instance those thrown when a package was not found, see #95957
            $value = null;
        }
        if ($value === null) {
            $value = $default;
            if (!empty($translateArguments)) {
                $value = vsprintf($value, $translateArguments);
            }
        }
        return $value;
    }

    protected static function translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys)
    {
        if (!self::$userConfiguration) {
            self::$userConfiguration = GeneralUtility::makeInstance(UserConfiguration::class);
        }

        if (self::$userConfiguration->usedForCore) {
            $isCoreExt = false;
            foreach (LanguageServiceXclassed::CORE_EXTENSIONS as $extension) {
                if (str_contains($id, 'EXT:' . $extension)) {
                    $isCoreExt = true;
                }
            }
            if ($isCoreExt) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        } elseif (self::$userConfiguration->crowdinIdentifier) {
            if (str_contains($id, 'EXT:' . self::$userConfiguration->extensionKey)
                || $extensionName === self::$userConfiguration->extensionKey) {
                $languageKey = 't3';
            } else {
                $languageKey = 'default';
            }
        }

        return LocalizationUtility::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }

    protected static function getLanguageService(?ServerRequestInterface $request = null): LanguageService
    {
        if (isset($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }
        $languageServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);
        if ($request !== null && ApplicationType::fromRequest($request)->isFrontend()) {
            $GLOBALS['LANG'] = $languageServiceFactory->createFromSiteLanguage($request->getAttribute('language')
                ?? $request->getAttribute('site')->getDefaultLanguage());
            return $GLOBALS['LANG'];
        }

        $user = $GLOBALS['BE_USER'] ?? null;
        if ($user && static::getConfigurationOption('enable', '0') === '1') {
            $user->user['lang'] = 't3';
        }
        $GLOBALS['LANG'] = $languageServiceFactory->createFromUserPreferences($user);
        return $GLOBALS['LANG'];
    }
}

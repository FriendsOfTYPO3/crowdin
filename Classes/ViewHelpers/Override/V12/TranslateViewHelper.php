<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\ViewHelpers\Override\V12;

use FriendsOfTYPO3\Crowdin\Traits\ConfigurationOptionsTrait;
use FriendsOfTYPO3\Crowdin\UserConfiguration;
use FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceXclassed;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

final class TranslateViewHelper extends AbstractViewHelper
{
    use ConfigurationOptionsTrait;

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    protected static ?UserConfiguration $userConfiguration = null;

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'Translation Key');
        $this->registerArgument('id', 'string', 'Translation ID. Same as key.');
        $this->registerArgument('default', 'string', 'If the given locallang key could not be found, this value is used. If this argument is not set, child nodes will be used to render the default');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string');
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('languageKey', 'string', 'Language key ("da" for example) or "default" to use. Also a Locale object is possible. If empty, use current locale from the request.');
        // @deprecated will be removed in TYPO3 v13.0. Deprecation is triggered in LocalizationUtility
        $this->registerArgument('alternativeLanguageKeys', 'array', 'Alternative language keys if no translation does exist. Ignored in non-extbase context. Deprecated, will be removed in TYPO3 v13.0');
    }

    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return mixed Rendered result
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext,
        );
    }

    /**
     * Return array element by key.
     *
     * @throws Exception
     * @throws \RuntimeException
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $key = $arguments['key'];
        $id = $arguments['id'];
        $default = (string)($arguments['default'] ?? $renderChildrenClosure() ?? '');
        $extensionName = $arguments['extensionName'];
        $translateArguments = $arguments['arguments'];

        // Use key if id is empty.
        if ($id === null) {
            $id = $key;
        }

        $id = (string)$id;
        if ($id === '') {
            throw new Exception('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = null;
        if ($renderingContext instanceof RenderingContext) {
            $request = $renderingContext->getRequest();
        }

        if (empty($extensionName)) {
            if ($request instanceof ExtbaseRequestInterface) {
                $extensionName = $request->getControllerExtensionKey();
            } elseif (str_starts_with($id, 'LLL:EXT:')) {
                $extensionName = substr($id, 8, strpos($id, '/', 8) - 8);
            } elseif ($default) {
                return self::handleDefaultValue($default, $translateArguments);
            } else {
                // Throw exception in case neither an extension key nor a extbase request
                // are given, since the "short key" shouldn't be considered as a label.
                throw new \RuntimeException(
                    'ViewHelper f:translate in non-extbase context needs attribute "extensionName" to resolve'
                    . ' key="' . $id . '" without path. Either set attribute "extensionName" together with the short'
                    . ' key "yourKey" to result in a lookup "LLL:EXT:your_extension/Resources/Private/Language/locallang.xlf:yourKey",'
                    . ' or (better) use a full LLL reference like key="LLL:EXT:your_extension/Resources/Private/Language/yourFile.xlf:yourKey".'
                    . ' Alternatively, you can also define a default value.',
                    1762943319
                );
            }
        }
        try {
            $locale = self::getUsedLocale($arguments['languageKey'], $request);
            $locale = self::overrideLocale($locale, $id, $extensionName);
            $value = LocalizationUtility::translate($id, $extensionName, $translateArguments, $locale, $arguments['alternativeLanguageKeys'] ?? null);
        } catch (\InvalidArgumentException) {
            // @todo: Switch to more specific Exceptions here - for instance those thrown when a package was not found, see #95957
            $value = null;
        }
        if ($value === null) {
            return self::handleDefaultValue($default, $translateArguments);
        }
        return $value;
    }

    /**
     * Ensure that a string is returned, if the underlying logic returns null, or cannot handle a translation
     */
    protected static function handleDefaultValue(string $default, ?array $translateArguments): string
    {
        if (!empty($translateArguments)) {
            return vsprintf($default, $translateArguments);
        }
        return $default;
    }

    protected static function getUsedLocale(Locale|string|null $languageKey, ?ServerRequestInterface $request): Locale|string|null
    {
        $user = $GLOBALS['BE_USER'] ?? null;
        if ($user && static::getConfigurationOption('enable', '0') === '1') {
            return 't3';
        }

        if ($languageKey !== null && $languageKey !== '') {
            return $languageKey;
        }
        if ($request) {
            return GeneralUtility::makeInstance(Locales::class)->createLocaleFromRequest($request);
        }
        return null;
    }

    protected static function overrideLocale(Locale|string|null $locale, string $id, string $extensionName): Locale|string|null
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
                $locale = 't3';
            } else {
                $locale = 'default';
            }
        } elseif (self::$userConfiguration->crowdinIdentifier) {
            if ($extensionName === self::$userConfiguration->extensionKey
                || str_contains($id, 'EXT:' . self::$userConfiguration->extensionKey)) {
                $locale = 't3';
            } else {
                $locale = 'default';
            }
        }

        return $locale;
    }
}

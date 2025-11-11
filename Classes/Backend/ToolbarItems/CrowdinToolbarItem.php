<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Backend\ToolbarItems;

use FriendsOfTYPO3\Crowdin\Traits\ConfigurationOptionsTrait;
use FriendsOfTYPO3\Crowdin\Xclass\LanguageServiceXclassed;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Form\Element\CheckboxToggleElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

class CrowdinToolbarItem implements ToolbarItemInterface
{
    use ConfigurationOptionsTrait;

    private readonly int $typo3Version;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly IconFactory $iconFactory
    ) {
        $this->typo3Version = (new Typo3Version())->getMajorVersion();

        if ($this->typo3Version >= 12) {
            $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
                JavaScriptModuleInstruction::create('@friendsoftypo3/crowdin/toolbar.js')
                    ->invoke('create', [
                        // options go here...
                    ])
            );
        } else {
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Crowdin/Toolbar/CrowdinMenu');
        }
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function getItem(): string
    {
        $title = 'Crowdin';

        $crowdin = [];
        $crowdin[] = '<span title="' . htmlspecialchars($title) . '">' . $this->getSpriteIcon('crowdin-toolbar-icon', 'inline') . '</span>';

        return implode(LF, $crowdin);
    }

    public function getDropDown(): string
    {
        $entries = [];

        $extensions = $this->getExtensionsCompatibleWithCrowdin();

        foreach ($extensions as $extension) {
            $icon = isset($extension['icon'])
                ? '<img src="' . htmlspecialchars($extension['icon']) . '" alt="' . htmlspecialchars($extension['name']) . '" style="width:16px">'
                : $this->getSpriteIcon($extension['iconIdentifier']);
            if ($this->typo3Version >= 12) {
                $entries[] = '<li' . ($extension['active'] ? ' style="background:#6DAAE0"' : '') . '>';
                $entries[] = '  <a href="#" class="crowdin-extension dropdown-item" role="menuitem" data-extension="' . $extension['key'] . '">';
                $entries[] = '    <span class="dropdown-item-columns">';
                $entries[] = '      <span class="dropdown-item-column dropdown-item-column-icon" aria-hidden="true">' .
                    $icon . '</span>';
                $entries[] = '      <span class="dropdown-item-column dropdown-item-column-title">' .
                    htmlspecialchars($extension['name']) . '</span>';
                $entries[] = '    </span>';
                $entries[] = '  </a>';
                $entries[] = '</li>';
            } else {
                $entries[] = '<div class="dropdown-table-row' . ($extension['active'] ? ' bg-primary' : '') . '">';
                $entries[] = '  <div class="dropdown-table-column dropdown-table-column-top dropdown-table-icon">';
                $entries[] = $icon;
                $entries[] = '  </div>';
                $entries[] = '  <div class="dropdown-table-column">';
                $entries[] = '<a href="#" class="crowdin-extension" data-extension="' . $extension['key'] . '">'
                    . htmlspecialchars($extension['name']) . '</a>';
                $entries[] = '  </div>';
                $entries[] = '</div>';
            }
        }

        $translationEnabled = static::getConfigurationOption('enable', '0') === '1';
        $enableCheckbox = $this->createToggleSwitch('crowdin_enable', $translationEnabled);

        $content = '';
        if ($this->typo3Version >= 12) {
            $content .= '<div class="float-end" style="width:30px;margin-top:3px">' . $enableCheckbox . '</div>';
            $content .= '<p class="h3 dropdown-headline" id="crowdin-dropdown-headline">Crowdin</p>';
            $content .= '<hr class="dropdown-divider" aria-hidden="true">';
            $content .= '<nav class="t3js-crowdinmenu">';
            $content .= '<ul class="dropdown-list" role="menu" aria-labelledby="crowdin-dropdown-headline">';
            $content .= implode(LF, $entries);
            $content .= '</ul>';
            $content .= '</nav>';
        } else {
            $content .= '<div class="float-end" style="width:30px;">' . $enableCheckbox . '</div>';
            $content .= '<h3 class="dropdown-headline">Crowdin</h3>';
            $content .= '<hr />';
            $content .= '<div class="dropdown-table">' . implode('', $entries) . '</div>';
        }

        return $content;
    }

    protected function createToggleSwitch(string $name, bool $enabled): string
    {
        $backupDebug = $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] ?? false;
        // We need this to avoid the debug output in the checkbox element
        $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = false;

        $data = [
            'tableName' => '__VIRTUAL__',
            'databaseRow' => ['uid' => 0],
            'fieldName' => $name,
            'parameterArray' => [
                'itemFormElValue' => $enabled ? 1 : 0,
                'fieldConf' => [
                    'config' => [
                        // "items" is needed for TYPO3 v11
                        'items' => [],
                        'readOnly' => false,
                    ],
                ],
                'itemFormElName' => $name,
                'itemFormElID' => $name,
            ],
            'processedTca' => [
                'columns' => [
                    $name => [
                        'config' => [
                            'type' => 'check',
                            'readOnly' => false,
                        ],
                    ],
                ],
            ],
        ];

        if ($this->typo3Version >= 13) {
            $toggleElement = GeneralUtility::makeInstance(CheckboxToggleElement::class);
            $toggleElement->setData($data);
        } else {
            $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
            $toggleElement = GeneralUtility::makeInstance(CheckboxToggleElement::class, $nodeFactory, $data);
        }
        $result = $toggleElement->render();

        $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = $backupDebug;

        return $result['html'];
    }

    protected function getExtensionsCompatibleWithCrowdin(): array
    {
        $extensionKey = static::getConfigurationOption('extension', 'typo3');

        $extensions = [];

        // TYPO3 Core is always compatible with Crowdin
        $extensions['_'] = [
            'key' => 'typo3',
            'name' => 'TYPO3 Core Extensions',  // TODO: translate!
            'iconIdentifier' => 'actions-brand-typo3',
            'active' => $extensionKey === 'typo3',
        ];

        $labelsDirectory = Environment::getVarPath() . '/labels/t3';

        if (is_dir($labelsDirectory)) {
            $candidateExtensions = GeneralUtility::get_dirs($labelsDirectory);
            // An extension is compatible with Crowdin if it has at least one label file in its labels directory
            $compatibleExtensions = array_filter($candidateExtensions, function ($extension) use ($labelsDirectory) {
                $languageDirectory = $labelsDirectory . '/' . $extension . '/Resources/Private/Language';
                $languageFiles = GeneralUtility::getFilesInDir($languageDirectory, 'xlf');
                return !empty($languageFiles);
            });

            $listUtility = GeneralUtility::makeInstance(ListUtility::class);
            $availableExtensions = $listUtility->getAvailableExtensions();
            $thirdPartyExtensions = array_diff_key($availableExtensions, array_flip(LanguageServiceXclassed::CORE_EXTENSIONS));

            foreach ($thirdPartyExtensions as $extension) {
                if (in_array($extension['key'], $compatibleExtensions)) {
                    $extensions[$extension['key']] = [
                        'key' => $extension['key'],
                        'name' => $extension['title'],
                        'icon' => $extension['icon'],
                        'active' => $extensionKey === $extension['key'],
                    ];
                }
            }
        }

        // Sort extensions by extension key (TYPO3 Core always first)
        ksort($extensions);

        return $extensions;
    }

    protected function getSpriteIcon(
        string $iconName,
        ?string $alternativeMarkupIdentifier = null
    ): string {
        $iconSize = $this->typo3Version >= 13
            ? \TYPO3\CMS\Core\Imaging\IconSize::SMALL
            : \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL;
        $icon = $this->iconFactory->getIcon($iconName, $iconSize)->render($alternativeMarkupIdentifier);

        return $icon;
    }

    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function hasDropDown(): bool
    {
        return true;
    }

    public function getIndex(): int
    {
        return 25;
    }

    /**
     * @ajax
     */
    public function toggleTranslationMode(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        if ($params === null) {
            // TODO: This happens in TYPO3 v12, understand the underlying issue
            $params = json_decode($request->getBody()->getContents(), true);
        }

        $enable = (bool)($params['enable'] ?? false);
        static::setConfigurationOption('enable', $enable ? '1' : '0');

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @ajax
     */
    public function setCurrentExtension(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getParsedBody();
        if ($params === null) {// TODO: This happens in TYPO3 v12, understand the underlying issue
            $params = json_decode($request->getBody()->getContents(), true);
        }

        $extension = $params['extension'] ?? '';
        if ($extension) {
            static::setConfigurationOption('extension', $extension);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}

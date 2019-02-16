<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\link\fields\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ElementTrait
{
    use BaseTrait {
        settings as baseSettings;
        attributes as baseAttributes;
    }

    /**
     * @var int
     */
    private $elementId;

    /**
     * @var Element|null
     */
    private $element;

    /**
     * @var string|string[]|null The source keys that this field can relate elements from (used if [[allowMultipleSources]] is set to true)
     */
    public $sources = '*';

    /**
     * @var string|null The source key that this field can relate elements from (used if [[allowMultipleSources]] is set to false)
     */
    public $source;

    /**
     * @var int|null The site that this field should relate elements from
     */
    public $targetSiteId;

    /**
     * @var bool Whether to allow multiple source selection in the settings
     */
    public $allowMultipleSources = true;

    /**
     * @var string|null The label that should be used on the selection input
     */
    public $selectionLabel;

    /**
     * @var bool Whether to allow the “Large Thumbnails” view mode
     */
    protected $allowLargeThumbsView = false;

    /**
     * @var string|null The view mode
     */
    public $viewMode;

    /**
     * @var string|null The JS class that should be initialized for the input
     */
    protected $inputJsClass;

    /**
     * Returns the element class associated with this link type.
     *
     * @return string
     */
    abstract protected static function elementType(): string;

    /**
     * @return string
     */
    abstract public function getElementText(): string;

    /**
     * @return string
     */
    abstract public function getElementUrl(): string;

    /**
     * @inheritdoc
     */
    public function settings(): array
    {
        return array_merge(
            $this->baseSettings(),
            [
                'sources',
                'targetSiteId',
                'viewMode',
                'selectionLabel'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            $this->baseAttributes(),
            [
                'elementId'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->getElementUrl();
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        if ($this->allowText && $this->overrideText !== null) {
            return $this->overrideText;
        }
        return $this->getElementText();
    }

    /**
     * @return int|null
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param $elementId
     */
    public function setElementId($elementId)
    {
        if (is_array($elementId)) {
            $elementId = reset($elementId);
        }
        $this->elementId = (int)$elementId;

        // Clear element cache on change
        if ($this->element === false ||
            ($this->element && $this->element->getId() !== $this->elementId)
        ) {
            $this->element = null;
        }
    }

    /**
     * @return ElementInterface|Element|null
     */
    protected function getElement()
    {
        if ($this->element === null) {
            $this->element = $this->lookupElement() ?: false;
        }

        return $this->element === false ? null : $this->element;
    }

    /**
     * @return ElementInterface|null
     */
    protected function lookupElement()
    {
        if ($this->elementId === null) {
            return null;
        }

        return Craft::$app->getElements()->getElementById(
            $this->elementId,
            static::elementType(),
            $this->targetSiteId
        );
    }

    /**
     * Normalizes the available sources into select input options.
     *
     * @return array
     */
    public function getSourceOptions(): array
    {
        $options = [];
        $optionNames = [];

        foreach ($this->availableSources() as $source) {
            // Make sure it's not a heading
            if (!isset($source['heading'])) {
                $options[] = [
                    'label' => $source['label'],
                    'value' => $source['key']
                ];
                $optionNames[] = $source['label'];
            }
        }

        // Sort alphabetically
        array_multisort($optionNames, SORT_NATURAL | SORT_FLAG_CASE, $options);

        return $options;
    }

    /**
     * Returns the sources that should be available to choose from within the field's settings
     *
     * @return array
     */
    protected function availableSources(): array
    {
        return Craft::$app->getElementIndexes()->getSources(static::elementType(), 'modal');
    }

    /**
     * Returns the HTML for the View Mode setting.
     *
     * @return string|null
     * @throws \yii\base\Exception
     */
    public function getViewModeFieldHtml()
    {
        $supportedViewModes = $this->supportedViewModes();

        if (count($supportedViewModes) === 1) {
            return null;
        }

        $viewModeOptions = [];

        foreach ($supportedViewModes as $key => $label) {
            $viewModeOptions[] = ['label' => $label, 'value' => $key];
        }

        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'selectField', [
            [
                'label' => Craft::t('app', 'View Mode'),
                'instructions' => Craft::t('app', 'Choose how the field should look for authors.'),
                'id' => 'viewMode',
                'name' => 'viewMode',
                'options' => $viewModeOptions,
                'value' => $this->viewMode
            ]
        ]);
    }

    /**
     * Returns the field’s supported view modes.
     *
     * @return array
     */
    protected function supportedViewModes(): array
    {
        $viewModes = [
            'list' => Craft::t('app', 'List'),
        ];

        if ($this->allowLargeThumbsView) {
            $viewModes['large'] = Craft::t('app', 'Large Thumbnails');
        }

        return $viewModes;
    }

    /**
     * Returns the site ID that target elements should have.
     *
     * @param ElementInterface|null $element
     * @return int
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function targetSiteId(ElementInterface $element = null): int
    {
        /** @var Element|null $element */
        if (Craft::$app->getIsMultiSite()) {
            if ($this->targetSiteId) {
                return $this->targetSiteId;
            }

            if ($element !== null) {
                return $element->siteId;
            }
        }

        return Craft::$app->getSites()->getCurrentSite()->id;
    }

    /**
     * Returns the default [[selectionLabel]] value.
     *
     * @return string The default selection label
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('app', 'Choose');
    }

    /**
     * Returns an array of variables that should be passed to the input template.
     *
     * @param Link $field
     * @param ElementInterface|null $element
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function inputTemplateVariables(
        Link $field,
        ElementInterface $element = null
    ): array {
        $selectionCriteria = $this->inputSelectionCriteria();
        $selectionCriteria['enabledForSite'] = null;
        $selectionCriteria['siteId'] = $this->targetSiteId($element);

        return [
            'jsClass' => $this->inputJsClass,
            'elementType' => static::elementType(),
            'id' => Craft::$app->getView()->formatInputId('elementId'),
            'fieldId' => $field->id,
            'storageKey' => 'field.' . $field->id,
            'name' => 'elementId',
            'elements' => [$this->getElement()],
            'sources' => $this->inputSources(),
            'criteria' => $selectionCriteria,
            'sourceElementId' => !empty($element->id) ? $element->id : null,
            'limit' => 1,
            'viewMode' => $this->viewMode(),
            'selectionLabel' => $this->selectionLabel ? Craft::t('site',
                $this->selectionLabel) : static::defaultSelectionLabel(),
        ];
    }

    /**
     * Returns any additional criteria parameters limiting which elements the field should be able to select.
     *
     * @return array
     */
    protected function inputSelectionCriteria(): array
    {
        return [];
    }

    /**
     * Returns the field’s current view mode.
     *
     * @return string
     */
    protected function viewMode(): string
    {
        $supportedViewModes = $this->supportedViewModes();
        $viewMode = $this->viewMode;

        if ($viewMode && isset($supportedViewModes[$viewMode])) {
            return $viewMode;
        }

        return 'list';
    }

    /**
     * Returns an array of the source keys the field should be able to select elements from.
     *
     * @return array|string
     */
    protected function inputSources()
    {
        if ($this->allowMultipleSources) {
            $sources = $this->sources;
        } else {
            $sources = [$this->source];
        }

        return $sources;
    }
}

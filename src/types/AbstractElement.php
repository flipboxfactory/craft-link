<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\link\fields\Link;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractElement extends AbstractType implements TypeInterface
{
    use ElementTrait;

    /**
     * The base template path to the field type templates
     */
    const BASE_TEMPLATE_PATH = AbstractType::BASE_TEMPLATE_PATH . '/element';

    /**
     * The settings template path
     */
    const SETTINGS_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/settings';

    /**
     * The input template path
     */
    const INPUT_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/input';

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        // If useTargetSite is in here, but empty, then disregard targetSiteId
        if (array_key_exists('useTargetSite', $config)) {
            if (empty($config['useTargetSite'])) {
                unset($config['targetSiteId']);
            }
            unset($config['useTargetSite']);
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getElementUrl(): string
    {
        if (!$element = $this->getElement()) {
            return '';
        }
        return (string)$element->getUrl();
    }

    /**
     * @inheritdoc
     */
    public function getElementText(): string
    {
        /** @var Element $element */
        if (!$element = $this->getElement()) {
            return '';
        }
        return (string)$element->title;
    }

    /**
     * @inheritdoc
     * @param ElementInterface|null $element
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     */
    public function inputHtml(Link $field, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::INPUT_TEMPLATE_PATH,
            [
                'field' => $field,
                'type' => $this,
                'input' => $this->inputTemplateVariables($field, $element)
            ]
        );
    }

    /**
     * Returns the HTML for the Target Site setting.
     *
     * @return string|null
     */
    public function getTargetSiteFieldHtml()
    {
        /** @var Element $class */
        $class = static::elementType();

        if (!Craft::$app->getIsMultiSite() || !$class::isLocalized()) {
            return null;
        }

        $type = mb_strtolower(static::displayName());
        $showTargetSite = !empty($this->targetSiteId);

        $html = Craft::$app->getView()->renderTemplateMacro(
            '_includes/forms',
            'checkboxField',
            [
                    [
                        'label' => Craft::t('app', 'Relate {type} from a specific site?', ['type' => $type]),
                        'name' => 'useTargetSite',
                        'checked' => $showTargetSite,
                        'toggle' => 'target-site-container'
                    ]
            ]
        ) .
            '<div id="target-site-container"' . (!$showTargetSite ? ' class="hidden"' : '') . '>';

        $siteOptions = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $siteOptions[] = [
                'label' => Craft::t('site', $site->name),
                'value' => $site->id
            ];
        }

        $html .= Craft::$app->getView()->renderTemplateMacro(
            '_includes/forms',
            'selectField',
            [
                [
                    'label' => Craft::t('app', 'Which site should {type} be related from?', ['type' => $type]),
                    'id' => 'targetSiteId',
                    'name' => 'targetSiteId',
                    'options' => $siteOptions,
                    'value' => $this->targetSiteId
                ]
            ]
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'elementId'
                    ],
                    'required',
                    'on' => [
                        static::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'elementId'
                    ],
                    'safe',
                    'on' => [
                        Model::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}

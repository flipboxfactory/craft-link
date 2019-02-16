<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use Craft;
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
        if (!$element = $this->getElement()) {
            return '';
        }
        return (string)$element->title;
    }

    /**
     * @inheritdoc
     * @param ElementInterface|null $element
     * @throws \Twig_Error_Loader
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

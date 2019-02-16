<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\link\fields\Link;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractType extends Model implements TypeInterface
{
    use BaseTrait;

    /**
     * The scenario used to validate input data
     */
    const SCENARIO_INPUT = 'input';

    /**
     * The base template path to the field type templates
     */
    const BASE_TEMPLATE_PATH = 'link/_components/fieldtypes/Link/types';

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
     * @throws \ReflectionException
     */
    public static function displayName(): string
    {
        $ref = new \ReflectionClass(static::class);
        return Craft::t('link', $ref->getShortName());
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::SETTINGS_TEMPLATE_PATH,
            [
                'type' => $this
            ]
        );
    }

    /**
     * Populate valid properties.  This occurs when we have a content value
     * and we need to populate it's contents on an existing TypeInterface
     *
     * @param array $properties
     */
    public function populate(array $properties)
    {
        // If the override text is empty, don't set it
        if ($overrideText = ArrayHelper::remove(
            $properties,
            'overrideText'
        )
        ) {

            $properties['overrideText'] = $overrideText;
        }

        foreach ($this->getProperties() as $key => $value) {
            if (array_key_exists($key, $properties)) {
                $this->{$key} = $properties[$key];
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function inputHtml(Link $field): string
    {
        return Craft::$app->getView()->renderTemplate(
            static::INPUT_TEMPLATE_PATH,
            [
                'field' => $field,
                'type' => $this
            ]
        );
    }

    /**
     * @return bool
     */
    public function validateInput(): bool
    {
        $currentScenario = $this->getScenario();
        $this->setScenario(self::SCENARIO_INPUT);

        $validates = $this->validate();

        $this->setScenario($currentScenario);
        return $validates;
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
                        'overrideText'
                    ],
                    'required',
                    'when' => function (self $model) {
                        return $model->getSettings()['requireText'] == true;
                    },
                    'message' => \flipbox\craft\link\Link::t('Link text is required'),
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'identifier',
                        'overrideText',
                        'target'
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

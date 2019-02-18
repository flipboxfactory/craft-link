<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Url extends AbstractType
{
    /**
     * The base template path to the field type templates
     */
    const BASE_TEMPLATE_PATH = AbstractType::BASE_TEMPLATE_PATH . '/url';

    /**
     * The settings template path
     */
    const SETTINGS_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/settings';

    /**
     * The input template path
     */
    const INPUT_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/input';

    /**
     * @var
     */
    public $url;

    /**
     * @var string|null The input’s placeholder text
     */
    public $placeholder;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \flipbox\craft\link\Link::t('Url');
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url ?: '';
    }

    /**
     * @inheritdoc
     */
    public function settings(): array
    {
        return array_merge(
            parent::settings(),
            [
                'placeholder'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'url'
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
                        'url'
                    ],
                    'url',
                    'defaultScheme' => '',
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'url'
                    ],
                    'required',
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'url'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}

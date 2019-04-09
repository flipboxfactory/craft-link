<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

use flipbox\craft\link\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
class Email extends AbstractType
{
    /**
     * The base template path to the field type templates
     */
    const BASE_TEMPLATE_PATH = AbstractType::BASE_TEMPLATE_PATH . '/email';

    /**
     * The settings template path
     */
    const SETTINGS_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/settings';

    /**
     * The input template path
     */
    const INPUT_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/input';

    /**
     * @var bool
     */
    public $useEmailAsDefaultText = true;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string|null The input’s placeholder text
     */
    public $placeholder;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Link::t('Email');
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        if ($this->allowText && $this->overrideText !== null) {
            return $this->overrideText;
        }

        return $this->useEmailAsDefaultText ? $this->email : null;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->email ? ('mailto:' . $this->email) :  '';
    }

    /**
     * @inheritdoc
     */
    public function settings(): array
    {
        return array_merge(
            parent::settings(),
            [
                'placeholder',
                'useEmailAsDefaultText'
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
                'email'
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
                        'email'
                    ],
                    'email',
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'email'
                    ],
                    'required',
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'email'
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

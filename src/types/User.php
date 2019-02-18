<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

use Craft;
use craft\elements\User as UserElement;
use flipbox\craft\link\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method UserElement getElement()
 */
class User extends AbstractElement
{
    /**
     * The base template path to the field type templates
     */
    const BASE_TEMPLATE_PATH = 'link/_components/fieldtypes/Link/types/element/user';

    /**
     * The settings template path
     */
    const SETTINGS_TEMPLATE_PATH = self::BASE_TEMPLATE_PATH . '/settings';

    /**
     * @var string
     */
    public $uri = 'mailto:{email}';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Link::t('User');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return UserElement::class;
    }

    /**
     * @inheritdoc
     */
    public function settings(): array
    {
        return array_merge(
            parent::settings(),
            [
                'uri'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getElementText(): string
    {
        /** @var \craft\elements\User $element */
        if (!$element = $this->getElement()) {
            return '';
        }
        return (string) $element->getFullName();
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getUrl(): string
    {
        if (!$element = $this->getElement()) {
            return '';
        }

        return Craft::$app->getView()->renderObjectTemplate(
            $this->uri,
            $element
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
                        'uri'
                    ],
                    'required',
                    'on' => [
                        self::SCENARIO_INPUT
                    ]
                ],
                [
                    [
                        'uri'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @param $uri
     * @return $this
     *
     * @deprecated Handling legacy setting attribute
     */
    public function setUriPath($uri)
    {
        $this->uri = $uri;
        return $this;
    }
}

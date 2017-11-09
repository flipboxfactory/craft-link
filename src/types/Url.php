<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\link\types;

use Craft;
use craft\base\ElementInterface;
use flipbox\link\fields\Link;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Url extends AbstractType
{
    /**
     * @var
     */
    public $url;

    /**
     * @var string
     */
    protected $identifier = 'url';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('link', 'Url');
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
    public function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'link/_components/fieldtypes/Link/types/url/settings',
            [
                'type' => $this
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
                        Model::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function inputHtml(Link $field, TypeInterface $type = null, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'link/_components/fieldtypes/Link/types/url/input',
            [
                'value' => $type,
                'element' => $element,
                'type' => $this,
                'field' => $field
            ]
        );
    }
}

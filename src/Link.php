<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\services\Fields;
use flipbox\craft\link\events\RegisterLinkTypes;
use flipbox\craft\link\fields\Link as LinkField;
use flipbox\craft\link\types\Asset;
use flipbox\craft\link\types\Category;
use flipbox\craft\link\types\Entry;
use flipbox\craft\link\types\TypeInterface;
use flipbox\craft\link\types\Url;
use flipbox\craft\link\types\User;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Link extends Plugin
{
    /**
     * The event name
     */
    const EVENT_REGISTER_TYPES = 'registerTypes';

    /**
     * The first party link types
     */
    const FIRST_PARTY_TYPES = [
        Asset::class,
        Category::class,
        Entry::class,
        Url::class,
        User::class
    ];

    /**
     * @var TypeInterface[]
     */
    private $types;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LinkField::class;
            }
        );
    }

    /**
     * @return array
     */
    public function findAllTypes()
    {
        if ($this->types === null) {
            $this->types = $this->registerTypes();
        }

        return $this->types;
    }

    /**
     * @param string $class
     * @return TypeInterface|null
     */
    public function findType(string $class)
    {
        return ArrayHelper::getValue(
            $this->findAllTypes(),
            $class
        );
    }

    /**
     * @return array
     */
    protected function registerTypes()
    {
        $event = new RegisterLinkTypes(
            [
            'types' => static::FIRST_PARTY_TYPES
            ]
        );

        $this->trigger(
            self::EVENT_REGISTER_TYPES,
            $event
        );

        return $this->resolveTypes($event->types);
    }

    /**
     * @param array $types
     * @return array
     */
    private function resolveTypes(array $types)
    {
        $validTypes = [];
        foreach ($types as $type) {
            if (!$type instanceof TypeInterface) {
                $type = new $type();
            }
            $validTypes[get_class($type)] = $type;
        }

        return $validTypes;
    }


    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\Craft::t()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo \flipbox\craft\link\Link::t('Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\Craft::t()]] for more details.
     *
     * @param  string $message  the message to be translated.
     * @param  array  $params   the parameters that will be used to replace the corresponding placeholders in the message.
     * @param  string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($message, $params = [], $language = null)
    {
        return \Craft::t('link', $message, $params, $language);
    }
}

<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

use craft\elements\Category as CategoryElement;
use flipbox\craft\link\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method null|CategoryElement findElement()
 */
class Category extends AbstractElement
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Link::t('Category');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return CategoryElement::class;
    }
}

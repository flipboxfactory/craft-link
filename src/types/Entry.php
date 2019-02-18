<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\types;

use craft\elements\Entry as EntryElement;
use flipbox\craft\link\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method EntryElement getElement()
 */
class Entry extends AbstractElement
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Link::t('Entry');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return EntryElement::class;
    }
}

<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use craft\elements\Asset as AssetElement;
use flipbox\craft\link\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method AssetElement getElement()
 */
class Asset extends AbstractElement
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Link::t('Asset');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return AssetElement::class;
    }
}

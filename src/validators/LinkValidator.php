<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\validators;

use craft\base\Element;
use flipbox\craft\link\types\TypeInterface;
use yii\validators\Validator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class LinkValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($element, $attribute)
    {
        /**
 * @var Element $element
*/

        /**
 * @var TypeInterface $value
*/
        $value = $element->$attribute;

        if ($value instanceof TypeInterface) {
            if (!$value->validateinput()) {
                $this->addError(
                    $element,
                    $attribute,
                    "Please fix the errors above."
                );
            }
        }
    }
}

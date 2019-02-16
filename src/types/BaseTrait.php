<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait BaseTrait
{
    /**
     * @var bool
     */
    public $allowText = true;

    /**
     * @var bool
     */
    public $requireText = false;

    /**
     * @var bool
     */
    public $showTarget = false;

    /**
     * @var string
     */
    public $target = "_self";

    /**
     * @var string|null
     */
    public $overrideText;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @return string
     */
    abstract public function getUrl(): string;

    /**
     * @return string|null
     */
    public function getText()
    {
        if ($this->allowText && $this->overrideText !== null) {
            return $this->overrideText;
        }

        return $this->getUrl();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        if ($this->identifier === null) {
            $this->identifier = StringHelper::randomString(8);
        }
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function setIdentifier(string $identifier): string
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return array
     */
    public function properties(): array
    {
        return array_diff($this->attributes(), $this->settings());
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->properties() as $property) {
            $properties[$property] = $this->$property;
        }

        return $properties;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            'identifier',
            'overrideText',
            'target'
        ];
    }

    /**
     * @inheritdoc
     */
    public function settings(): array
    {
        return [
            'showTarget',
            'allowText',
            'requireText'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): array
    {
        $settings = [];

        foreach ($this->settings() as $attribute) {
            $settings[$attribute] = $this->$attribute;
        }

        return $settings;
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function getHtml(array $attributes = []): string
    {
        $defaults = [
            'href' => $this->getUrl(),
            'title' => $this->getText(),
        ];

        if ($this->showTarget && $this->target) {
            $defaults['target'] = $this->target;
        }

        $text = ArrayHelper::remove($attributes, 'text', $this->getText());

        $properties = array_filter(
            array_merge(
                $defaults,
                $attributes
            )
        );

        array_walk(
            $properties,
            function (&$v, $k) {
                $v = $k . '="' . $v . '"';
            }
        );

        return '<a ' . implode(' ', $properties) . '>' . $text . '</a>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getHtml();
    }
}

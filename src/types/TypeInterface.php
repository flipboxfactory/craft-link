<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\types;

use flipbox\craft\link\fields\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
interface TypeInterface
{
    /**
     * @return string
     */
    public static function displayName(): string;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $identifier
     * @return static
     */
    public function setIdentifier(string $identifier);

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return string|null
     */
    public function getText();

    /**
     * @param array $attributes
     * @return string
     */
    public function getHtml(array $attributes = []): string;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @return array
     */
    public function getProperties(): array;

    /**
     * @return bool
     */
    public function validateInput(): bool;

    /**
     * @param array $properties
     * @return mixed
     */
    public function populate(array $properties);

    /**
     * @return string
     */
    public function settingsHtml(): string;

    /**
     * @param Link $field
     * @return string
     */
    public function inputHtml(Link $field): string;
}

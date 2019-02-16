<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/link/license
 * @link       https://www.flipboxfactory.com/software/link/
 */

namespace flipbox\craft\link\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use flipbox\craft\link\Link as LinkPlugin;
use flipbox\craft\link\types\TypeInterface;
use flipbox\craft\link\validators\LinkValidator;
use flipbox\craft\link\web\assets\settings\Settings;
use yii\db\Schema;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Link extends Field
{

    /**
     * Type objects that have been configured via the admin
     *
     * @var TypeInterface[]
     */
    protected $types = [];

    /**
     * Raw configurations that have been configured via the admin
     *
     * @var array
     */
    protected $typeConfigs = [];

    /**
     * @return TypeInterface[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getTypes()
    {
        // Make sure all un-resolved configs are resolved
        $this->resolveConfigs();

        return $this->types;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setTypes(array $types)
    {
        foreach ($types as $identifier => $type) {
            $identifier = is_array($type) ? ArrayHelper::getValue($type, 'identifier', $identifier) : $identifier;
            $this->typeConfigs[$identifier] = $type;
        }
        return $this;
    }

    /**
     * @param string $identifier
     * @return TypeInterface|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getType(string $identifier)
    {
        // Is it already an object?
        if (!array_key_exists($identifier, $this->types)) {
            // Can we create it?
            if (!$type = $this->createFromConfig($identifier)) {
                return null;
            }

            $this->types[$identifier] = $type;
        }

        return clone $this->types[$identifier];
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('link', 'Link');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettingsHtml()
    {

        $view = Craft::$app->getView();

        $view->registerAssetBundle(Settings::class);

        return $view->renderTemplate(
            'link/_components/fieldtypes/Link/settings',
            [
                'field' => $this,
                'types' => LinkPlugin::getInstance()->findAllTypes(),
                'namespace' => $view->getNamespace()
            ]
        );
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate(
            'link/_components/fieldtypes/Link/input',
            [
                'field' => $this,
                'value' => $value,
                'element' => $element
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof TypeInterface) {
            $value = array_merge(
                [
                    'identifier' => $value->getIdentifier(),
                ],
                $value->getProperties()
            );
        }

        return Db::prepareValueForDb($value);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettings(): array
    {
        $settings = parent::getSettings();

        // Merge the type settings
        foreach ($this->getTypes() as $identifier => $type) {
            $settings['types'][$identifier] = array_merge(
                ['class' => get_class($type)],
                $type->getSettings()
            );
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return [
            [
                LinkValidator::class
            ]
        ];
    }

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return array|TypeInterface|mixed|object|null
     * @throws \yii\base\InvalidConfigException
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof TypeInterface) {
            return $value;
        }

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (!is_array($value)) {
            return null;
        }

        // Get the type by identifier
        if ($identifier = ArrayHelper::remove($value, 'identifier')) {
            $type = $this->getType($identifier);
        } else {
            if ($class = ArrayHelper::remove($value, 'class')) {
                $type = new $class;
            }
        }

        if (empty($type) || !$type instanceof TypeInterface) {
            return null;
        }

        // When saving via the admin, there may be multiple 'types' configured
        if ($types = ArrayHelper::remove($value, 'types')) {
            $value = array_merge(
                ArrayHelper::remove($types, $identifier, []),
                $value
            );
        }

        // Populate
        $type->populate($value);

        return $type;
    }

    /**
     * Create objects from all (remaining) configurations
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function resolveConfigs()
    {
        foreach ($this->typeConfigs as $identifier => $config) {
            $this->resolveConfig($identifier, $config);
        }
        $this->typeConfigs = [];
    }

    /**
     * @param string $identifier
     * @param array  $config
     * @return TypeInterface|null
     * @throws \yii\base\InvalidConfigException
     */
    private function resolveConfig(string $identifier, array $config)
    {
        // Create new
        /**
 * @var TypeInterface $type 
*/
        if (!$type = $this->createType($config)) {
            return null;
        }

        $type->setIdentifier($identifier);

        // Cache it
        $this->types[$identifier] = $type;

        return $type;
    }

    /**
     * @param $type
     * @return array|object|null
     * @throws \yii\base\InvalidConfigException
     */
    private function createType($type)
    {
        if ($type instanceof TypeInterface) {
            return $type;
        }

        if (!is_array($type)) {
            $type = ['class' => $type];
        }

        $type = Craft::createObject(
            $type
        );

        if (!$type instanceof TypeInterface) {
            return null;
        }

        return $type;
    }

    /**
     * @param string $identifier
     * @return TypeInterface|null
     * @throws \yii\base\InvalidConfigException
     */
    private function createFromConfig(string $identifier)
    {
        if (!$config = ArrayHelper::remove($this->typeConfigs, $identifier)) {
            return null;
        }

        return $this->resolveConfig($identifier, $config);
    }
}

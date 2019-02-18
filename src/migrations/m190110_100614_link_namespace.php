<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://raw.githubusercontent.com/flipboxfactory/craft-link/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-link
 */

namespace flipbox\craft\link\migrations;

use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\records\Field;
use flipbox\craft\link\fields\Link;

class m190110_100614_link_namespace extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $records = Field::find()
            ->andWhere(
                [
                'type' => "flipbox\\link\\fields\\Link"
                ]
            )
            ->all();

        $success = true;

        /**
 * @var Field $record
*/
        foreach ($records as $record) {
            $record->type = Link::class;

            $settings = $record->settings ?? [];
            if (is_string($settings)) {
                $settings = Json::decodeIfJson($settings);
            }

            $types = ArrayHelper::remove($settings, 'types', []);
            foreach ($types as &$type) {
                $class = $type['class'] ?? null;

                // Match our first party links
                if (StringHelper::startsWith(
                    $class,
                    "flipbox\\link"
                )
                ) {
                    // Adjust namespacing
                    $type['class'] = StringHelper::replace(
                        $class,
                        "flipbox\\link",
                        "flipbox\\craft\\link"
                    );

                    // Allow text not set?
                    if (!ArrayHelper::keyExists(
                        'allowText',
                        $type
                    )
                    ) {
                        $type['allowText'] = (bool)(ArrayHelper::remove(
                            $type,
                            'showText',
                            ArrayHelper::remove(
                                $type,
                                'overrideText'
                            )
                        ));
                    }

                    // Remove old
                    ArrayHelper::remove($type, 'limit');
                    ArrayHelper::remove($type, 'localizeRelations');
                    ArrayHelper::remove($type, 'useSingleFolder');
                    ArrayHelper::remove($type, 'defaultUploadLocationSource');
                    ArrayHelper::remove($type, 'defaultUploadLocationSubpath');
                    ArrayHelper::remove($type, 'singleUploadLocationSource');
                    ArrayHelper::remove($type, 'singleUploadLocationSubpath');
                    ArrayHelper::remove($type, 'restrictFiles');
                    ArrayHelper::remove($type, 'allowedKinds');

                    // Should already be removed
                    ArrayHelper::remove($type, 'overrideText');
                    ArrayHelper::remove($type, 'showText');
                }
            }

            // Set types back to settings
            $settings['types'] = $types;

            // Update settings
            $record->settings = $settings;

            // Save
            if (!$record->save(true, ['type, settings'])) {
                $success = false;
            }
        }

        return $success;
    }
}

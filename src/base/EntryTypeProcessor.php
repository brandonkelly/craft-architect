<?php
/**
 * Architect plugin for Craft CMS 3.x
 *
 * CraftCMS plugin to generate content models from JSON data.
 *
 * @link      https://pennebaker.com
 * @copyright Copyright (c) 2018 Pennebaker
 */

namespace pennebaker\architect\base;

use Craft;
use craft\models\EntryType;
use craft\models\FieldLayout;

/**
 * EntryTypeProcessor defines the common interface to be implemented by plugin classes.
 *
 * @author    Pennebaker
 * @package   Architect
 * @since     2.0.0
 */
class EntryTypeProcessor extends Processor
{
    /**
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        $section = Craft::$app->sections->getSectionByHandle($item['sectionHandle']);
        $item['sectionId'] = $section->id;

        $sectionEntryTypes = $section->getEntryTypes();
        if ($section->type === 'single') {
            $item['id'] = $sectionEntryTypes[0]->id;
            $item['name'] = $sectionEntryTypes[0]->name;
            $item['handle'] = $sectionEntryTypes[0]->handle;
        } else {
            foreach ($sectionEntryTypes as $sectionEntryType) {
                if ($sectionEntryType->handle === $item['handle']) {
                    $item['id'] = $sectionEntryType->id;
                }
            }
        }

        $entryType = (isset($item['id'])) ? Craft::$app->sections->getEntryTypeById($item['id']) : new EntryType();
        $entryType->sectionId = $section->id;
        $entryType->name = $item['name'];
        $entryType->handle = $item['handle'];
        $entryType->hasTitleField = $item['hasTitleField'];
        $entryType->titleLabel = $item['titleLabel'];
        $entryType->titleFormat = $item['titleFormat'];

        if (isset($item['fieldLayout'])) {
            foreach ($item['fieldLayout'] as $tab => $fields) {
                foreach ($item['fieldLayout'][$tab] as $k => $fieldHandle) {
                    $item['fieldLayout'][$tab][$k] = Craft::$app->fields->getFieldByHandle($fieldHandle)->id;
                }
            }
            foreach ($item['requiredFields'] as $k => $fieldHandle) {
                $item['requiredFields'][$k] = Craft::$app->fields->getFieldByHandle($fieldHandle)->id;
            }
            $fieldLayout = Craft::$app->fields->assembleLayout($item['fieldLayout'], $item['requiredFields']);
        } else {
            $fieldLayout = new FieldLayout();
        }
        $fieldLayout->type = Entry::class;

        $item['fieldLayout'] = $fieldLayout;

        $entryType->setFieldLayout($fieldLayout);

        return [$entryType, null];
    }

    /**
     * @param $item
     * @param bool $update
     *
     * @return bool|object
     *
     * @throws \Throwable
     * @throws \craft\errors\EntryTypeNotFoundException
     */
    public function save($item, bool $update = false)
    {
        return Craft::$app->sections->saveEntryType($item);
    }
}
<?php

namespace VuFindCollapseExpand\RecordDriver\Feature;

/**
 * Logic for collapse and expand functionality.
 * This trait adds some accessor methods to your RecordDriver.
 *
 * PHP version 8
 * Copyright (C) The Library of Tuebingen University 2025
 *
 * @category VuFindCollapseExpand
 * @package  VuFindCollapseExpand\RecordDriver
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

trait CollapseExpandTrait
{
    use \VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;

    public function getSubrecordIds()
    {
        return $this->getField('subrecord_ids') ?? [];
    }

    public function isSubRecord(): bool
    {
        return $this->fields['_isSubRecord'] ?? false;
    }

    public function hasSubRecords(): bool
    {
        if (null !== ($collection = $this->getSubRecords())) {
            return 0 < $collection->count();
        }
        return false;
    }

    public function getSubRecords()
    {
        return $this->fields['_subRecords'] ?? null;
    }

    /**
     * This function is used by collapse and expand to get the expand field
     */
    public function getExpandField()
    {
        return $this->fields[$this->collapseExpandConfig->getExpandField()] ?? '';
    }
}

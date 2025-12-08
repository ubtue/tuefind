<?php

/*
 * Copyright 2021 (C) Bibliotheksservice-Zentrum Baden-
 * Württemberg, Konstanz, Germany
 *
 * Copyright 2025 (C) Bibliothek - Universität Tübingen
 * Author Steven Lolong (steven.lolong@uni-tuebingen.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

namespace VuFindCollapseExpand\RecordDriver;

/**
 * This trait adds some accessor methods to your RecordDriver
 *
 * Trait SubrecordTrait
 * @package VuFindCollapseExpand\RecordDriver
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * 
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
trait SubrecordTrait
{
    /**
     * @return bool
     */
    public function isSubRecord(): bool
    {
        return isset($this->fields['_isSubRecord']) ?
            $this->fields['_isSubRecord'] : false;
    }

    /**
     * @return bool
     */
    public function hasSubRecords(): bool
    {
        if (null !== ($collection = $this->getSubRecords())) {
            return 0 < $collection->count();
        }
        return false;
    }

    /**
     * @return array|null
     */
    public function getSubRecords()
    {
        return isset($this->fields['_subRecords']) ?
            $this->fields['_subRecords'] : null;
    }
}
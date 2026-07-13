<?php

namespace KrimDok\RecordDriver;

use function is_array;

class SolrDefault extends \TueFind\RecordDriver\SolrMarc
{
    public function getGenres()
    {
        return $this->fields['genre'] ?? [];
    }

    /**
     * @return array
     */
    public function getInstitutsSystematik()
    {
        if (isset($this->fields['instituts_systematik2']) && !empty($this->fields['instituts_systematik2'])) {
            return $this->fields['instituts_systematik2'];
        } else {
            return [];
        }
    }

    /**
     * Get an array of all the ISILs in the record.
     *
     * @return array
     */
    public function getIsils()
    {
        return $this->fields['isil'] ?? [];
    }

    /**
     * Get local signatures of the current record.
     *
     * @return array
     */
    public function getLocalSignatures()
    {
        return isset($this->fields['local_signature']) && is_array($this->fields['local_signature']) ?
            $this->fields['local_signature'] : [];
    }

    /**
     * Get the start page of the item that contains this record (i.e. MARC 773q of a
     * journal).
     *
     * @return string
     */
    public function getPageCount()
    {
        return $this->fields['page_count'] ?? '';
    }

    public function isAvailableForPDA()
    {
        return $this->fields['available_for_pda'] ?? false;
    }

    public function isAvailableInTuebingen()
    {
        return $this->getLocalSignatures() != [];
    }

    public function showAvailabilityInTuebingen()
    {
        return $this->isAvailableInTuebingen() && !empty($this->getLocalSignatures());
    }
}

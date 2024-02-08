<?php

namespace KrimDok\RecordDriver;

class SolrMarc extends SolrDefault
{
    const SUBITO_BROKER_ID = 'KRIMDOK';

    // ISIL to e.g. determine the correct default local data block. Should be overridden in child classes.
    const ISIL_DEFAULT = 'DE-2619'; // KrimDok - Kriminologische Bibliographie

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @param bool $extended Whether to return a keyed array with the following
     * keys:
     * - heading: the actual subject heading chunks
     * - type: heading type
     * - source: source vocabulary
     *
     * @return array
     */
    public function getAllSubjectHeadings($extended = false)
    {
        // Get default headings from parent
        $defaultHeadings = parent::getAllSubjectHeadings($extended);

        // Add custom headings
        $customHeadings = [];
        $fields = $this->getMarcReader()->getFields('689');
        $current = [];
        $currentID = 0;
        foreach ($fields as $field) {
            $id = $field['i1'];
            if ($id != $currentID && !empty($current)) {
                $customHeadings[] = $current;
                $current = [];
            }
            foreach ($field['subfields'] as $subfield) {
                if (!is_numeric($subfield['code']) && strlen($subfield['data']) > 2) {
                    if (!$extended) {
                        $current[] = $subfield['data'];
                    } else {
                        $current[] = [
                            'heading' => $subfield['data'],
                            'type' => 'subject',
                            'source' => '',
                        ];
                    }
                }
            }
            $currentID = $id;
        }
        if (!empty($current)) {
            $customHeadings[] = $current;
        }

        $fields = $this->getMarcReader()->getFields('LOK');
        foreach ($fields as $field) {
            $current = [];
            $subfields = $field['subfields'];
            $firstSubfieldData = $subfields[0]['data'] ?? null;
            if ($firstSubfieldData === '689  ') {
                foreach ($subfields as $subfield) {
                    if ($subfield['code'] === 'a' && strlen($subfield['data']) > 1) {
                        if (!$extended) {
                            $current[] = $subfield['data'];
                        } else {
                            $current[] = [
                                'heading' => $subfield['data'],
                                'type' => 'subject',
                                'source' => '',
                            ];
                        }
                    }
                }
            }
            if (!empty($current)) {
                $customHeadings[] = $current;
            }
        }

        // merge, unique, sort
        $headings = array_merge($defaultHeadings, $customHeadings);
        $headings = array_unique($headings, SORT_REGULAR);
        uasort($headings, function($a, $b) {
            $aSortKey = implode('#', $a);
            $bSortKey = implode('#', $b);
            return strnatcmp($aSortKey, $bSortKey);
        });

        return $headings;
    }

    public function isAlbertKrebsLibraryRecord(): bool
    {
        return in_array('kreb', $this->getRecordSelectors());
    }

    public function getAlbertKrebsLibraryAvailability(): ?array
    {
        $availability = ['signature' => null, 'holding' => null];

        foreach ($this->getLOKBlockDefault() as $lokField) {
            $isHolding = false;
            $isSignature = false;
            $a = null;
            $c = null;
            foreach ($lokField['subfields'] as $subfield) {
                if ($subfield['code'] == '0') {
                    if ($subfield['data'] == '852 1')
                        $isSignature = true;
                    elseif ($subfield['data'] == '86630')
                        $isHolding = true;
                } elseif ($subfield['code'] == 'a') {
                    $a = $subfield['data'];
                } elseif ($subfield['code'] == 'c') {
                    $c = $subfield['data'];
                }
            }

            if ($isSignature && $c !== null)
                $availability['signature'] = $c;
            elseif ($isHolding && $a !== null)
                $availability['holding'] = str_replace('Bestand Albert-Krebs-Bibliothek: ', '', $a);
        }

        if ($availability['signature'] != null || $availability['holding'] != null)
            return $availability;

        return null;
    }

    public function showAvailabilityInAlbertKrebsLibrary(): bool
    {
        return $this->isAlbertKrebsLibraryRecord() && ($this->getAlbertKrebsLibraryAvailability() !== null);
    }
}

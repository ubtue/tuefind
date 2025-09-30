<?php

namespace TueFind\Marc;

class MarcReader extends \VuFind\Marc\MarcReader
{
    public function getFieldsDelimiter($spec, $delimiter='|'): array
    {
        $matches = [];

        $tags = explode($delimiter, $spec);
        if(!empty($tags)) {
            foreach($tags as $tag) {
                $fields = $this->getFields($tag);
                if(!empty($fields)) {
                    $matches = array_merge($matches, $fields);
                }
            }
        }

        return $matches;
    }


    public function getMarcRecord() {
        return $this->getMarcReader()->getRawMarcRecords();
    }

    /**
     * Get subfields as assoc array for a given field array.
     * Repeatable subfields are not supported!
     */
    public function getSubfieldsAssoc(array $field): array {
        $result = [];
        foreach ($field['subfields'] as $subfield) {
            $result[$subfield['code']] = $subfield['data'];
        }
        return $result;
    }

}

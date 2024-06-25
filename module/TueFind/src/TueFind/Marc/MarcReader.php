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

}

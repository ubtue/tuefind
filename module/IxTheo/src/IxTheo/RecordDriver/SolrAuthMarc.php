<?php

namespace IxTheo\RecordDriver;

class SolrAuthMarc extends SolrAuthDefault {
    public function getExternalSubsystems(): array
    {
        $subsystemLinks = [
            ['title' => 'RelBib', 'url' => 'https://relbib.de/AuthorityRecord/' . urlencode($this->getUniqueID()), 'label' => 'REL'],
            ['title' => 'Index Biblicus', 'url' => 'https://bible.ixtheo.de/AuthorityRecord/' . urlencode($this->getUniqueID()), 'label' => 'BIB'],
            ['title' => 'Index Canonicus', 'url' => 'https://canonlaw.ixtheo.de/AuthorityRecord/' . urlencode($this->getUniqueID()), 'label' => 'CAN']
        ];

        $result = [];
        $result[] = ['title' => 'Index Theologicus', 'url' => 'https://ixtheo.de/AuthorityRecord/' . urlencode($this->getUniqueID()), 'label' => 'IXT'];
        foreach ($this->getSubsystems() as $subsystem) {
            foreach ($subsystemLinks as $subsystemLink) {
                if ($subsystemLink['label'] == $subsystem) {
                    $result[] = $subsystemLink;
                }
            }
        }

        return $result;
    }
}

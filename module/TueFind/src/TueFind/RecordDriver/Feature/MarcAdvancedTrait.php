<?php
namespace TueFind\RecordDriver\Feature;


trait MarcAdvancedTrait
{
    use \VuFind\RecordDriver\Feature\MarcBasicTrait, \VuFind\RecordDriver\Feature\MarcAdvancedTrait  {
         \VuFind\RecordDriver\Feature\MarcAdvancedTrait::getNewerTitles insteadof \VuFind\RecordDriver\Feature\MarcBasicTrait;
         \VuFind\RecordDriver\Feature\MarcAdvancedTrait::getPreviousTitles insteadof \VuFind\RecordDriver\Feature\MarcBasicTrait;
         \VuFind\RecordDriver\Feature\MarcAdvancedTrait::getSeriesFromMARC as getVuFindSeriesFromMARC;
         \VuFind\RecordDriver\Feature\MarcAdvancedTrait::getSeries as getVuFindSeries;
    }

    public function getSubfieldsWithCustomSeparator($currentField, $subfields, $subfieldSeparatorMap = null) {
        // Start building a line of text for the current field
        $matches = '';
        // Loop through all subfields, collecting results that match the whitelist;
        // note that it is important to retain the original MARC order here!
        $allSubfields = $currentField->getSubfields();
        if (!empty($allSubfields)) {
            foreach ($allSubfields as $currentSubfield) {
                $code = $currentSubfield->getCode();
                if (in_array($code, $subfields)) {
                    $separator = !is_null($subfieldSeparatorMap) && isset($subfieldSeparatorMap[$code]) ?
                                 $subfieldSeparatorMap[$code] : ' ';
                    // Grab the current subfield value and act on it if it is
                    // non-empty:
                    $data = trim($currentSubfield->getData());
                    if (!empty($data)) {
                        $matches .= !empty($matches) ? $separator . $data : $data;
                    }
                }
            }
        }

        return $matches;
    }

    public function getSeries() {
        return $this->getVuFindSeries();
    }

    public function getSeriesFromMARC($fieldInfo) {
        $seriesSeparators = [ 'c' => ', ', 't' => ', ' ];
        $matches = [];

        // Loop through the field specification....
        foreach ($fieldInfo as $field => $subfields) {
            // Did we find any matching fields?
            $series = $this->getMarcReader()->getFields($field);

            if (is_array($series)) {
                foreach ($series as $currentField) {

                    $name = '';
                    foreach($currentField['subfields'] as $subFields) {
                        $code = $subFields['code'];
                        if (in_array($code, $subfields)) {
                          $separator = !is_null($seriesSeparators) && isset($seriesSeparators[$code]) ? $seriesSeparators[$code] : ' ';
                          // Grab the current subfield value and act on it if it is
                          // non-empty:
                          $data = trim($subFields['data']);
                          if (!empty($data)) {
                              $name .= !empty($name) ? $separator . $data : $data;
                          }
                        }
                    }

                    if (!empty($name)) {
                        $currentArray = ['name' => $name];

                        // Can we find a number in subfield v?  (Note that number is
                        // always in subfield v regardless of whether we are dealing
                        // with 440, 490, 800 or 830 -- hence the hard-coded array
                        // rather than another parameter in $fieldInfo).
                        $number = $this->getMarcReader()->getSubfield($currentField, 'v');
                        if (!empty($number)) {
                            $currentArray['number'] = $number;
                        }
                        // Save the current match:
                        $matches[] = $currentArray;
                    }
                }
            }
        }

        return $matches;
    }


    public function makeDescriptionLinksClickable($description) {
        // c.f. https://stackoverflow.com/questions/5341168/best-way-to-make-links-clickable-in-block-of-text (211027)
        return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="blank_">$1</a>', $description);
    }


    public function getPhysicalDescriptions() {
         return $this->getFieldArray('300', ['a', 'b', 'c', 'e', 'f', 'g'], true, ', ');
    }


    public function isLastArrayKey(&$array_, $key) {
        $last = array_key_last($array_);
        return $last === $key;
    }


    public function getCorporateAuthorsFromMarc($corporateAuthorTag, $subfieldsToExtract) {
        $fields=$this->getMarcReader()->getFields($corporateAuthorTag);
        $separators = ['a' => '. '];
        $corporateAuthors = [];
        foreach ($fields as $field) {
            $corporateAuthor = '';
            // Remove superfluous entries
            $subfields = array_filter($field['subfields'],
                             function($subfield) use ($subfieldsToExtract) {
                                 return in_array($subfield['code'], $subfieldsToExtract);
                              }
            );

            // Sort subfields to given order
            usort($subfields, function ($a, $b) use ($subfieldsToExtract) {
                  $posA = array_search($a['code'], $subfieldsToExtract);
                  $posB = array_search($b['code'], $subfieldsToExtract);
                  return $posA - $posB;
            });

            foreach($subfields as $subfield) {
               $code = $subfield['code'];
               $separator = array_key_exists($code, $separators) ? $separators[$code] : ' ';
               $data = trim($subfield['data']);
               if ($code == 'd')
                   $data = '(' . $data . ')';
               $corporateAuthor .= !$this->isLastArrayKey($subfields, key($subfields)) ? $data . $separator : $data;
            }
            array_push($corporateAuthors, $corporateAuthor);
        }
        return $corporateAuthors;
   }


   public function getCorporateAuthors() {
        return array_merge(
            $this->getCorporateAuthorsFromMarc('110', ['a', 'e', 'n', 'g', 'c', 'd']),
            $this->getCorporateAuthorsFromMarc('111', ['a', 'e', 'n', 'g', 'c', 'd']),
            $this->getCorporateAuthorsFromMarc('710', ['a', 'e', 'n', 'g', 'c', 'd']),
            $this->getCorporateAuthorsFromMarc('711', ['a', 'e', 'n', 'g', 'c', 'd'])
        );
    }


    public function getCountRemarks() {
        return $this->getFieldArray('515', ['a'], true, ', ');
    }

    /**
     * Take the information from MARC instead of SOLR, but with a different
     * mechanism than VuFind\Feature\MarcBasicTrait.
     *
     * For Solr, we want to search ISBN-10 as well as ISBN-13,
     * but in the frontend we only want to show the form as written in
     * MARC.
     *
     * Also, 9 is not always there, so we will show a if it is missing.
     *
     * If there are any changes here, also consider changing the Solr import
     * depending on the circumstances.
     *
     * See issue #3389
     *
     * @return array
     */
    public function getISBNs() : array
    {
        // Intentionally omit 020z and 773z
        // Prefer 0209 over 020a
        // Show 0209 in the exact way of writing, without normalization

        // We cannot just call getFieldArray once, because it will NOT keep the subfield order
        $isbns = [];
        foreach ($this->getMarcReader()->getFields('020') as $field) {
            $subfields = $this->getMarcReader()->getSubfieldsAssoc($field);
            if (isset($subfields['9']))
                $isbns[] = $subfields['9'];
            elseif (isset($subfields['a']))
                $isbns[] = $subfields['a'];
        }

        return array_unique($isbns);
    }

    /**
     * Override the parent behaviour, just return the first ISBN as-is,
     * without favoring ISBN-10 over 13.
     *
     * @return type
     */
    public function getCleanISBN() {
        $isbns = $this->getISBNs();
        return $isbns[0] ?? false;
    }


}

<?php

namespace IxTheo\Search\Backend\Solr;

use function define;

define('_BIB_REF_MAPS_PATH_', \TueFind\Utility::TUELIB_DIR . '/bibleRef/'); // Must end with backslash!
define('_BIB_REF_CMD_PARAMS_', implode(' ', [_BIB_REF_MAPS_PATH_ . 'bible_aliases.map',
       _BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_code.map', _BIB_REF_MAPS_PATH_ . 'books_of_the_bible_to_canonical_form.map',
       _BIB_REF_MAPS_PATH_ . 'pericopes_to_codes.map']));

class SearchHandler extends \TueFindSearch\Backend\Solr\SearchHandler
{
    public const BIBLE_RANGE_PARSER = 'bibleRangeParser';
    public const BIBLE_REFERENCE_COMMAND = \TueFind\Utility::BIN_DIR . '/bib_ref_to_codes_tool';
    public const BIBLE_REFERENCE_COMMAND_PARAMETERS = _BIB_REF_CMD_PARAMS_;
    public const CANONES_RANGE_PARSER = 'canonesRangeParser';
    public const CANONES_REFERENCE_COMMAND = \TueFind\Utility::BIN_DIR . '/canon_law_ref_to_codes_tool';

    protected function createQueryString($search, $advanced = false)
    {
        switch ($this->specs['RangeType']) {
            case QueryBuilder::BIBLE_RANGE_HANDLER:
                $rangeReferences = $this->parseBibleReference($search);
                return $this->translateRangesToSearchString($rangeReferences, self::BIBLE_RANGE_PARSER);
            case QueryBuilder::CANONES_RANGE_HANDLER:
                $rangeReferences = $this->parseCanonesReference($search);
                return $this->translateRangesToSearchString($rangeReferences, self::CANONES_RANGE_PARSER);
            default:
                return parent::createQueryString($search, $advanced);
        }
    }

    protected function getBibleReferenceCommand($search)
    {
        setlocale(LC_CTYPE, 'de_DE.UTF-8');
        return implode(' ', [
            self::BIBLE_REFERENCE_COMMAND,
            '--query',
            escapeshellarg($search),
            self::BIBLE_REFERENCE_COMMAND_PARAMETERS,
        ]);
    }

    protected function getCanonesReferenceCommand($search)
    {
        return implode(' ', [
            self::CANONES_REFERENCE_COMMAND,
            '--query',
            escapeshellarg($search),
        ]);
    }

    protected function parseBibleReference($search)
    {
        if (!empty($search)) {
            $cmd = $this->getBibleReferenceCommand($search);
            exec($cmd, $output, $return_var);
            return $output;
        }
        return [];
    }

    protected function parseCanonesReference($search)
    {
        if (!empty($search)) {
            $cmd = $this->getCanonesReferenceCommand($search);
            exec($cmd, $output, $return_var);
            return $output;
        }
        return [];
    }
}

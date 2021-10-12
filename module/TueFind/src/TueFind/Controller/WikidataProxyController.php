<?php

namespace TueFind\Controller;

/**
 * Use Wikidata API to search for specific information (e.g. a picture)
 * Example call: https://ptah.ub.uni-tuebingen.de/wikidataproxy/load?search=Martin%20Luther
 */
class WikidataProxyController extends \VuFind\Controller\AbstractBase
                              implements \VuFind\I18n\Translator\TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    public function loadAction()
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $parameters = [];
        parse_str($query, $parameters);

        if (isset($parameters['id'])) {
            $entities = $this->wikidata()->getEntities([$parameters['id']]);
            $entity = $this->getFirstMatchingEntity($entities);
            $image = $this->getBestImageFromEntity($entity);
            return $this->generateResponse($image);
        } else {
            if (!isset($parameters['search']))
                throw new \VuFind\Exception\BadRequest('Invalid request parameters.');

            $searches = $parameters['search'];
            if (!is_array($searches))
                $searches = [$searches];
            $language = $this->getTranslatorLocale();

            // P18: image
            // P569: birthYear
            // P570: deathYear
            $mandatoryFields = ['P18'];
            $filters = [];
            if (isset($parameters['birthYear']))
                $filters['P569'] = ['value' => $parameters['birthYear'], 'type' => 'year'];
            if (isset($parameters['deathYear']))
                $filters['P570'] = ['value' => $parameters['deathYear'], 'type' => 'year'];

            if (count($filters) == 0)
                throw new \Exception('No suitable image found (at least one additional filter must be given!)');

            foreach ($searches as $search) {
                try {
                    $entities = $this->wikidata()->searchAndGetEntities($search, $language);
                    $entity = $this->getFirstMatchingEntity($entities, $filters, ['P18']);
                    $image = $this->getBestImageFromEntity($entity);
                    return $this->generateResponse($image);
                } catch (\Exception $e) {
                    // just continue and search for next image
                    continue;
                }
            }
        }
        throw new \Exception('No suitable image found');
    }

    protected function normalizeHeaderContent($artist) {
        // We use htmlspecialchars_decode(htmlentities()) because HTTP headers only support ASCII.
        // This way we can keep HTML special characters without breaking non-ascii-characters.
        // It is necessary to set ENT_HTML5 instead of default ENT_HTML401,
        // because the entity table is a lot bigger (also contains e.g. cyrillic entities).
        // See also: get_html_translation_table
        return htmlspecialchars_decode(htmlentities(preg_replace("'(\r?\n)+'", ', ', trim(strip_tags($artist))), ENT_COMPAT | ENT_HTML5));
    }

    protected function generateResponse(&$image) {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', $image['mime']);
        // See RFC 5988 + http://www.otsukare.info/2011/07/12/using-http-link-header-for-cc-licenses
        if (isset($image['licenseUrl']))
            $response->getHeaders()->addHeaderLine('Link', htmlspecialchars_decode(htmlentities('<'.$image['licenseUrl'].'>; rel="license"; title="' . $this->normalizeHeaderContent($image['license']) . '"')));
        if (isset($image['artist']))
            $response->getHeaders()->addHeaderLine('Artist', $this->normalizeHeaderContent($image['artist']));
        $response->setContent($image['image']);
        return $response;
    }

    protected function getBestImageFromEntity(&$entity) {
        $images = $entity->claims->P18 ?? [];
        foreach ($images as $image) {
            $imageFilename = $image->mainsnak->datavalue->value ?? null;

            // TIFFs will be skipped, since they are not supported in Firefox+Chrome
            // Example: Helmut Kohl
            if (preg_match('"\.tiff?$"i', $imageFilename))
                continue;

            return $this->wikidata()->getImage($imageFilename);
        }

        throw new \Exception('No suitable image found');
    }

    /**
     * Get first matching element for a single entry
     *
     * @param json $entities
     * @param array $filters
     * @param array $mandatoryFields
     * @return \DOMElement or null if not found
     */
    protected function getFirstMatchingEntity(&$entities, $filters=[], $mandatoryFields=[]) {
        foreach ($entities->entities as $entity) {
            $skip = false;

            // must have values
            foreach ($mandatoryFields as $field) {
                if (!isset($entity->claims->$field[0]->mainsnak->datavalue->value)) {
                    $skip = true;
                    break;
                }
            }

            // filters
            foreach ($filters as $field => $filterProperties) {
                // filters
                if (!isset($entity->claims->$field)) {
                    $skip = true;
                    break;
                }
                else {
                    foreach ($entity->claims->$field as $fieldValue) {
                        $compareValue = $fieldValue->mainsnak->property;
                        if ($filterProperties['type'] == 'year') {
                            $compareValue = $fieldValue->mainsnak->datavalue->value->time;
                            $compareValue = date('Y', strtotime($compareValue));
                        }

                        if ($compareValue != $filterProperties['value']) {
                            $skip = true;
                            break;
                        }
                    }
                }
            }

            if (!$skip)
                return $entity;
        }

        throw new \Exception('No valid entity found');
    }
}

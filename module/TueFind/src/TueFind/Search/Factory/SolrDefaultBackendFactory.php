<?php

namespace TueFind\Search\Factory;

use TueFindSearch\Backend\Solr\HandlerMap;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\Connector;

use function array_key_exists;

class SolrDefaultBackendFactory extends AbstractSolrBackendFactory implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * Create the SOLR connector
     * Set the language code.
     *
     * @return Connector
     */
    protected function createConnector()
    {
        $timeout = $this->getIndexConfig('timeout', 30);
        $searchConfig = $this->configManager->getConfigObject($this->searchConfig);
        $defaultFields = $searchConfig->General->default_record_fields ?? '*';

        if (($searchConfig->Explain->enabled ?? false) && !str_contains($defaultFields, 'score')) {
            $defaultFields .= ',score';
        }

        $this->setTranslator($this->serviceLocator->get(\Laminas\Mvc\I18n\Translator::class));
        $currentLang = $this->getTranslatorLocale();

        // On the Solr side we use different naming scheme
        // so map traditional and simplified chinese accordingly
        $chineseLangMap = [ 'zh' => 'hant', 'zh-cn' => 'hans'];
        if (array_key_exists($currentLang, $chineseLangMap)) {
            $currentLang = $chineseLangMap[$currentLang];
        }

        $handlers = [
            'select' => [
                'fallback' => true,
                'defaults' => ['fl' => $defaultFields,
                               'lang' => $currentLang,
                               'defType' => 'multiLanguageQueryParser',
                               'df' => 'allfields',
                              ],
                'appends'  => ['fq' => []],
            ],
            'terms' => [
                'functions' => ['terms'],
            ],
            'morelikethis' => [
                'functions' => ['similar'],
            ],
        ];

        foreach ($this->getHiddenFilters() as $filter) {
            array_push($handlers['select']['appends']['fq'], $filter);
        }

        // Careful: Inherited TueFind HandlerMap is used here, see "use" statement at top
        $connector = new $this->connectorClass(
            $this->getSolrUrl(),
            new HandlerMap($handlers),
            function (string $url) use ($timeout) {
                return $this->createHttpClient(
                    $timeout,
                    $this->getHttpOptions($url),
                    $url
                );
            },
            $this->uniqueKey
        );

        if ($this->logger) {
            $connector->setLogger($this->logger);
        }

        if ($cache = $this->createConnectorCache($searchConfig)) {
            $connector->setCache($cache);
        }

        return $connector;
    }
}

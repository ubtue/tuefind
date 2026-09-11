<?php

namespace TueFind\Search\Factory;

use TueFindSearch\Backend\Solr\QueryBuilder;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\HandlerMap;

class SolrAuthBackendFactory extends \VuFind\Search\Factory\SolrAuthBackendFactory implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * Create TueFind's custom Solr query builder while retaining
     * VuFind 11.1 configuration handling.
     */
    protected function createQueryBuilder()
    {
        $specs = $this->loadSpecs();

        $defaultDismax = $this->getIndexConfig(
            'default_dismax_handler',
            'dismax'
        );

        $builder = new QueryBuilder(
            $specs,
            $defaultDismax
        );

        $builder->setLuceneHelper(
            $this->createLuceneSyntaxHelper()
        );

        return $builder;
    }

    /**
     * Create the Solr authority connector.
     *
     * Based on VuFind 11.1 AbstractSolrBackendFactory::createConnector(),
     * with TueFind-specific language and multiLanguageQueryParser settings.
     */
    protected function createConnector()
    {
        $timeout = $this->getIndexConfig('timeout', 30);

        $searchConfig = $this->configManager->getConfigObject(
            $this->searchConfig
        );

        /*
         * Preserve the old TueFind behaviour (*,score) when
         * default_record_fields is not explicitly configured.
         */
        $defaultFields
            = $searchConfig->General->default_record_fields
            ?? '*,score';

        /*
         * Match VuFind 11.1 behavior when Explain is enabled.
         */
        if (
            ($searchConfig->Explain->enabled ?? false)
            && !str_contains($defaultFields, 'score')
        ) {
            $defaultFields .= ',score';
        }

        /*
         * TueFind customization:
         * determine the current language for the custom
         * multiLanguageQueryParser.
         */
        $this->setTranslator(
            $this->serviceLocator->get(
                \Laminas\Mvc\I18n\Translator::class
            )
        );

        $currentLang = $this->getTranslatorLocale();

        /*
         * Solr uses different identifiers for traditional and
         * simplified Chinese.
         */
        $chineseLangMap = [
            'zh' => 'hant',
            'zh-cn' => 'hans',
        ];

        $currentLang
            = $chineseLangMap[$currentLang]
            ?? $currentLang;

        /*
         * Start from VuFind 11.1's handler structure and add
         * TueFind-specific parameters to the select handler.
         */
        $handlers = [
            'select' => [
                'fallback' => true,
                'defaults' => [
                    'fl' => $defaultFields,

                    // TueFind-specific settings:
                    'lang' => $currentLang,
                    'defType' => 'multiLanguageQueryParser',
                    'df' => 'allfields',
                ],
                'appends' => [
                    'fq' => [],
                ],
            ],

            // VuFind 11.1 using terms instead of term.
            'terms' => [
                'functions' => ['terms'],
            ],

            // VuFind 11.1:
            'morelikethis' => [
                'functions' => ['similar'],
            ],
        ];

        foreach ($this->getHiddenFilters() as $filter) {
            $handlers['select']['appends']['fq'][] = $filter;
        }

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

        /*
         * Let VuFind 11.1 create the cache.
         * This also handles the current Laminas cache configuration format.
         * TueFind does not need to duplicate the Laminas cache implementation.
         */
        if ($cache = $this->createConnectorCache($searchConfig)) {
            $connector->setCache($cache);
        }

        return $connector;
    }
}

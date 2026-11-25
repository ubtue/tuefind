<?php

namespace IxTheo\Search\Factory;

use IxTheo\Search\Backend\Solr\Backend;
use IxTheo\Search\Backend\Solr\LuceneSyntaxHelper;
use IxTheo\Search\Backend\Solr\QueryBuilder;
use TueFindSearch\Backend\Solr\HandlerMap;
use TueFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\Connector;

use function array_key_exists;

class SolrDefaultBackendFactory extends \TueFind\Search\Factory\SolrDefaultBackendFactory implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {
        $backend = new Backend($connector);
        $pageSize = $this->getIndexConfig(
            'record_batch_size',
            100
        );

        $maxClauses = $this->getIndexConfig(
            'maxBooleanClauses',
            $pageSize
        );

        if ($pageSize > 0 && $maxClauses > 0) {
            $backend->setPageSize(min($pageSize, $maxClauses));
        }

        $backend->setQueryBuilder($this->createQueryBuilder());
        $backend->setSimilarBuilder($this->createSimilarBuilder());
        if ($this->logger) {
            $backend->setLogger($this->logger);
        }
        $manager = $this->serviceLocator->get(\VuFind\RecordDriver\PluginManager::class);
        $factory = new RecordCollectionFactory([$manager, 'getSolrRecord'], $this->serviceLocator);
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }

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

    /**
     * Create the query builder.
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        $specs   = $this->loadSpecs();
        $config = $this->configManager->getConfigObject($this->mainConfig);
        $defaultDismax = $config->Index->default_dismax_handler ?? 'dismax';
        $builder = new QueryBuilder($specs, $defaultDismax);

        // Configure builder:
        $search = $this->configManager->getConfigObject($this->searchConfig);
        $caseSensitiveBooleans = $search->General->case_sensitive_bools ?? true;
        $caseSensitiveRanges = $search->General->case_sensitive_ranges ?? true;
        $helper = new LuceneSyntaxHelper($caseSensitiveBooleans, $caseSensitiveRanges);
        $builder->setLuceneHelper($helper);
        return $builder;
    }
}

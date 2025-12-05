<?php
namespace IxTheo\Search\Factory;

use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\Connector;
use TueFindSearch\Backend\Solr\HandlerMap;
use TueFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory;
use IxTheo\Search\Backend\Solr\Backend;
use IxTheo\Search\Backend\Solr\LuceneSyntaxHelper;
use IxTheo\Search\Backend\Solr\QueryBuilder;

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
        $backend->setQueryBuilder($this->createQueryBuilder());
        $backend->setSimilarBuilder($this->createSimilarBuilder());
        if ($this->logger) {
            $backend->setLogger($this->logger);
        }
        $manager = $this->serviceLocator->get(\VuFind\RecordDriver\PluginManager::class);
        $factory = new RecordCollectionFactory([$manager, 'getSolrRecord']);
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }


    /**
     * Create the SOLR connector
     * Set the language code
     *
     * @return Connector
     */
    protected function createConnector()
    {
        $timeout = $this->getIndexConfig('timeout', 30);
        $config = $this->configManager->get($this->mainConfig);
        $this->setTranslator($this->serviceLocator->get(\Laminas\Mvc\I18n\Translator::class));
        $current_lang = $this->getTranslatorLocale();

        // On the Solr side we use different naming scheme
        // so map traditional and simplified chinese accordingly
        $chinese_lang_map = [ "zh" => "hant", "zh-cn" => "hans"];
        if (array_key_exists($current_lang, $chinese_lang_map))
            $current_lang = $chinese_lang_map[$current_lang];

        $handlers = [
            'select' => [
                'fallback' => true,
                'defaults' => ['fl' => '*,score', 'lang' => $current_lang,
                               'defType' => 'multiLanguageQueryParser', 'df' => 'allfields'
                              ],
                'appends'  => ['fq' => []],
            ],
            'term' => [
                'functions' => ['terms'],
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

        if (!empty($searchConfig->SearchCache->adapter)) {
            $cacheConfig = $searchConfig->SearchCache->toArray();
            $options = $cacheConfig['options'] ?? [];
            if (empty($options['namespace'])) {
                $options['namespace'] = 'Index';
            }
            if (empty($options['ttl'])) {
                $options['ttl'] = 300;
            }
            $settings = [
                'name' => $cacheConfig['adapter'],
                'options' => $options,
            ];
            $cache = $this->serviceLocator
                ->get(\Laminas\Cache\Service\StorageAdapterFactory::class)
                ->createFromArrayConfiguration($settings);
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
        $config = $this->configManager->get($this->mainConfig);
        $defaultDismax = isset($config->Index->default_dismax_handler)
                         ? $config->Index->default_dismax_handler : 'dismax';
        $builder = new QueryBuilder($specs, $defaultDismax);

        // Configure builder:
        $search = $this->configManager->get($this->searchConfig);
        $caseSensitiveBooleans = isset($search->General->case_sensitive_bools)
                                 ? $search->General->case_sensitive_bools : true;
        $caseSensitiveRanges = isset($search->General->case_sensitive_ranges)
                               ? $search->General->case_sensitive_ranges : true;
        $helper = new LuceneSyntaxHelper($caseSensitiveBooleans, $caseSensitiveRanges);
        $builder->setLuceneHelper($helper);
        return $builder;
    }
}

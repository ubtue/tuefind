<?php
namespace TueFind\Search\Factory;

use Psr\Container\ContainerInterface;
use VuFind\Config\ConfigManagerInterface;
use VuFind\Config\Config;
use TueFindSearch\Backend\Solr\Backend;
use TueFind\Search\Solr\InjectFulltextMatchIdsListener;


class AbstractSolrBackendFactory extends \VuFind\Search\Factory\SolrDefaultBackendFactory {
   /**
     * Create service
     *
     * @param ContainerInterface $sm      Service manager
     * @param string             $name    Requested service name (unused)
     * @param array              $options Extra options (unused)
     *
     * @return Backend
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $sm, $name, array $options = null)
    {
        $this->serviceLocator = $sm;
        $this->setup($sm);
        $this->configManager = $this->getService(ConfigManagerInterface::class);
        if ($this->serviceLocator->has(\VuFind\Log\Logger::class)) {
            $this->logger = $this->serviceLocator->get(\VuFind\Log\Logger::class);
        }
        $connector = $this->createConnector();
        $backend   = $this->createBackend($connector);
        $backend->setIdentifier($name);
        $this->createListeners($backend);
        return $backend;
    }


    protected function createListeners(\VuFindSearch\Backend\Solr\Backend $backend) {
        parent::createListeners($backend);
        $events = $this->serviceLocator->get('SharedEventManager');
        $search = $this->configManager->get($this->searchConfig);
//        if (isset($search->FulltextMatchIds)) {
            $this->getInjectFulltextMatchIdsListener($backend, $search)->attach($events);
//        }
    }


    protected function getInjectFulltextMatchIdsListener(\VuFindSearch\Backend\BackendInterface $backend,
         Config $search
    ) {
        return new InjectFulltextMatchIdsListener($backend);
    }
}

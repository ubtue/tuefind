<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;

class RedirectService extends AbstractDbService implements RedirectServiceInterface
{
    /**
     * Insert an URL with an optional group.
     * Timestamp will be added automatically, for later statistical analysis.
     *
     * @param string $url   The redirect target
     * @param string $group A group which might be use for later statistics
     */
    public function insertUrl(string $url, string $group) {
        $conn = $this->entityManager->getConnection();
        /**
         * the 'tuefind_redirect' table does not have a primary key and Doctrine does not support entities without primary keys, so we use a raw SQL query to insert the data.
         **/
        $conn->executeStatement(
            'INSERT INTO tuefind_redirect (url, group_name) VALUES (:url, :group)',
            ['url' => $url, 'group' => $group]
        );
    }
}
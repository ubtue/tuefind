<?php

/*
 * Copyright (C) 2021 Bibliotheks-Service Zentrum, Konstanz, Germany
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace VuFindCollapseExpand\Search\Params;

use Psr\Container\ContainerInterface;
use VuFindCollapseExpand\Search\Solr\AuthorParams;
use VuFindCollapseExpand\Search\Solr\Params;

/**
 * Search params Factory
 *
 * @package VuFindCollapseExpand\Search\Params
 *
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author Robert Lange <lange@ub.uni-leipzig.de>
 *
 * change from Grouping to CollapseExpand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
class Factory
{
    /**
     * Factory for Solr params object.
     *
     * @param ContainerInterface $container
     *
     * @return \VuFind\Search\Solr\Params
     */
    public static function getSolr(ContainerInterface $container)
    {
        $config = $container->get(\VuFind\Config::class);
        $options = $container->get(\VuFind\SearchOptionsPluginManager::class)->get('solr');
        $collapseExpandConfig = $container->get(\VuFindCollapseExpand\Config\CollapseExpand::class);
        $params = new Params($options, $config, null, $collapseExpandConfig);

        return $params;
    }

    /**
     * Factory for Solr params object.
     *
     * @param ContainerInterface $container
     *
     * @return \VuFind\Search\SolrAuthor\Params
     */
    public static function getSolrAuthor(ContainerInterface $container)
    {
        $config = $container->get(\VuFind\Config::class);
        $options = $container->get(\VuFind\SearchOptionsPluginManager::class)->get('solrauthor');
        $collapseExpandConfig = $container->get(\VuFindCollapseExpand\Config\CollapseExpand::class);
        $params = new AuthorParams($options, $config, null, $collapseExpandConfig);

        return $params;
    }
}

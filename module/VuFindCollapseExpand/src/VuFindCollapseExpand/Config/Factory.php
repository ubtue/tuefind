<?php

/*
 * Copyright 2021 (C) Bibliotheksservice-Zentrum Baden-
 * Württemberg, Konstanz, Germany
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
 *
 */

namespace VuFindCollapseExpand\Config;

use Psr\Container\ContainerInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Session\Container;

/**
 * Description of Factory
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * 
 * 
 * Factory for Grouping config
 * @package VuFindCollapseExpand\Config
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * Change section config from the 'Index' to 'CollapseExpand'
 * 
 */
class Factory
{
    public static function getCollapseExpandGrouping(ContainerInterface $container)
    {
        // collapse_expand has a special Config section 'CollapseExpand'
        $config = $container->get('VuFind\Config')->get('config')->get('CollapseExpand');
        $sesscontainer = new Container(
            'collapse_expand_grouping',
            $container->get('VuFind\SessionManager')
        );
        $response = $container->get('Response');
        $cookie = $container->get('Request')->getCookie();
        // return new Grouping($config, $sesscontainer, $response, $cookie);
        return new CollapseExpandGrouping($config, $sesscontainer, $response, $cookie);
    }
}
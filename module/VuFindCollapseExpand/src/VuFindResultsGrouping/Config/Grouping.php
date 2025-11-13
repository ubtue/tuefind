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

use Laminas\Http\Header\SetCookie;
use Laminas\Session\Container as SessionContainer;

/**
 * Class for storing grouping options
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 *
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */
class Grouping
{
    protected $config;
    /**
     * @var SessionContainer
     */
    protected $container;
    protected $response;
    protected $cookie;

    public function __construct(
        $config,
        SessionContainer $container,
        $response,
        $cookiedata
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->response = $response;
        $this->cookie = $cookiedata;
        $this->restoreFromCookie();
    }

    public function restoreFromCookie()
    {
        if (isset($this->cookie->group)) {
            $this->container->offsetSet('group', $this->cookie->group);
        }
        if (isset($this->cookie->group_field)) {
            $this->container->offsetSet('group_field', $this->cookie->group_field);
        }
        if (isset($this->cookie->group_limit)) {
            $this->container->offsetSet('group_limit', $this->cookie->group_limit);
        }
        if (isset($this->cookie->group_expand)) {
            $this->container->offsetSet('group_expand', $this->cookie->group_expand);
        }
    }

    /**
     * @param $post
     *
     * @return array
     */
    public function store($post)
    {
        $params = $this->getCurrentSettings();
        if (array_key_exists('group', $post)) {
            $cookie = new SetCookie(
                'group',
                (int)$post['group'],
                time() + 14 * 24 * 60 * 60,
                '/'
            );
            $header = $this->response->getHeaders();
            $header->addHeader($cookie);
            $this->container->offsetSet('group', $post['group']);
            $params['group'] = $post['group'];
        }
        if (isset($post['group_field'])) {
            $cookie = new SetCookie(
                'group_field',
                $post['group_field'],
                time() + 14 * 24 * 60 * 60,
                '/'
            );
            $header = $this->response->getHeaders();
            $header->addHeader($cookie);
            $this->container->offsetSet('group_field', $post['group_field']);
            $params['field'] = $post['group_field'];
        }
        if (isset($post['group_limit'])) {
            $cookie = new SetCookie(
                'group_limit',
                $post['group_limit'],
                time() + 14 * 24 * 60 * 60,
                '/'
            );
            $header = $this->response->getHeaders();
            $header->addHeader($cookie);
            $this->container->offsetSet('group_limit', $post['group_limit']);
            $params['limit'] = $post['group_limit'];
        }
        if (isset($post['group_expand'])) {
            $cookie = new SetCookie(
                'group_expand',
                $post['group_expand'],
                time() + 14 * 24 * 60 * 60,
                '/'
            );
            $header = $this->response->getHeaders();
            $header->addHeader($cookie);
            $this->container->offsetSet('group_expand', $post['group_expand']);
            $params['limit'] = $post['group_expand'];
        }
        return $params;
    }

    public function getCurrentSettings(): array
    {
        $params = [
            'group' => $this->container->offsetExists('group') ? (bool)$this->container->offsetGet('group') : (bool)$this->config->get('group'),
            'group_field' => $this->container->offsetExists('group_field') ? $this->container->offsetGet('group_field') : explode(':', $this->config->get('group.field')),
            'group_limit' => $this->container->offsetExists('group_limit') ? $this->container->offsetGet('group_limit') : $this->config->get('group.limit'),
            'group_expand' => $this->container->offsetExists('group_expand') ? $this->container->offsetGet('group_expand') : $this->config->get('group.expand'),
        ];
        return $params;
    }

    public function isActive(): bool
    {
        $conf = $this->getCurrentSettings();
        return $conf['group'] == 1;
    }
}
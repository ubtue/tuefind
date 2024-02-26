<?php

namespace TueFind\Auth;

class Database extends \VuFind\Auth\Database
{
    protected function collectParamsFromRequest($request)
    {
        $params = parent::collectParamsFromRequest($request);
        $params['tuefind_institution'] = $request->getPost()->get('tuefind_institution', null);
        $params['tuefind_country'] = $request->getPost()->get('tuefind_country', null);
        return $params;
    }

    protected function createUserFromParams($params, $table)
    {
        $user = parent::createUserFromParams($params, $table);
        $user->setInstitution($params['tuefind_institution']);
        $user->setCountry($params['tuefind_country']);
        return $user;
    }
}

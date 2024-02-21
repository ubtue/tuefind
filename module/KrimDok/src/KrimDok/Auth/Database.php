<?php

namespace KrimDok\Auth;

class Database extends \VuFind\Auth\Database
{

    protected function collectParamsFromRequest($request)
    {
        $params = parent::collectParamsFromRequest($request);
        $additionalParams = [
            'krimdok_subscribed_to_newsletter' => boolval($request->getPost()->get('krimdok_subscribed_to_newsletter', false)),
        ];
        foreach ($additionalParams as $param => $default) {
            $params[$param] = $request->getPost()->get($param, $default);
        }
        return $params;
    }

    protected function createUserFromParams($params, $table)
    {
        $user = parent::createUserFromParams($params, $table);
        $user->setSubscribedToNewsletter($params['krimdok_subscribed_to_newsletter']);
        return $user;
    }
}

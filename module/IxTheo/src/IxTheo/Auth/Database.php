<?php

namespace IxTheo\Auth;
use VuFind\Exception\Auth as AuthException, Laminas\Crypt\Password\Bcrypt;

class Database extends \TueFind\Auth\Database
{
    public static $appellations = ["", "Mr", "Ms"];
    public static $titles = ["", "B.A.", "M.A.", "M.Div.", "Dipl. Theol.", "Dr.", "Ph.D.", "Th.D.", "Prof.", "Lic. theol.", "Lic. iur. can.", "Student", "Other"];

    /**
     * Collect parameters from request and populate them.
     *
     * @param Request $request Request object containing new account details.
     *
     * @return string[]
     */
    protected function collectParamsFromRequest($request)
    {
        $params = parent::collectParamsFromRequest($request);

        $additionalParams = [
            'ixtheo_title' => '',
            'ixtheo_appellation' => ''
        ];
        foreach ($additionalParams as $param => $default) {
            $params[$param] = $request->getPost()->get($param, $default);
        }
        return $params;
    }

    /**
     * Create a user row object from given parametes.
     *
     * @param string[]  $params Parameters returned from collectParamsFromRequest()
     * @param UserTable $table  The VuFind user table
     *
     * @return \VuFind\Db\Row\User A user row object
     */
    protected function createUserFromParams($params, $table)
    {
        $user = parent::createUserFromParams($params, $table);
        $user->setAppellation(in_array($params['ixtheo_appellation'], Database::$appellations) ? $params['ixtheo_appellation'] : $user->getAppellation());
        $user->setTitle(in_array($params['ixtheo_title'], Database::$titles) ? $params['ixtheo_title'] : $user->getTitle());
        $user->setUserType(\IxTheo\Utility::getUserTypeFromUsedEnvironment());

        return $user;
    }

    public function create($request)
    {
        $user = parent::create($request);

        // Update the TAD access flag
        // This cannot be executed in "createUserFromParams"
        // since the ID will be generated afterwards in the parent
        // after persist() is called on the entity manager.
        exec("/usr/local/bin/set_tad_access_flag.sh " . $user->getId());

        return $user;
    }

    public function authenticate($request)
    {
        $user = parent::authenticate($request);
        $userSystem = $user->getUserType();
        $currentSystem = \IxTheo\Utility::getUserTypeFromUsedEnvironment();

        // Write an additional log file to detect which ixtheo-users are actually used to log into bibstudies+churchlaw.
        // This is technically allowed right now and might lead to problems, so we would like to keep track of the users
        // to see if we can easily prevent them from switching instances at a later point.
        $logEntry = '[' . date('Y-m-d H:i:s') . '] User "' . $user->getUsername() . '" with type "' . $userSystem . '" logging into instance "' . basename(getenv('VUFIND_LOCAL_DIR')) . '"' . PHP_EOL;
        file_put_contents('/usr/local/var/log/tuefind/vufind_auth.log', $logEntry, FILE_APPEND);

        if ($userSystem != $currentSystem)
            throw new AuthException($this->translate('authentication_error_wrong_system',
                                    ['%%currentSystem%%' => $currentSystem,
                                     '%%userSystem%%' => $userSystem]));

        return $user;
    }

}

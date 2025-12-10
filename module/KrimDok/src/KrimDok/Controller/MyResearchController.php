<?php

namespace KrimDok\Controller;

class MyResearchController extends \TueFind\Controller\MyResearchController
{
    public function newsletterAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $submitted = $this->formWasSubmitted('submit');
        if ($submitted) {
            $user->setSubscribedToNewsletter(boolval($this->getRequest()->getPost()->subscribed));
            $this->getAuthManager()->updateSession($user);
        }

        return $this->createViewModel(['subscribed' => $user->isSubscribedToNewsletter(),
                                       'submitted'  => $submitted]);
    }

    protected function getProfileParams()
    {
        $params = [
            'krimdok_subscribed_to_newsletter' => ['default' => '0', 'getter' => 'getSubscribedToNewsletter', 'setter' => 'setSubscribedToNewsletter'],
        ];
        return array_merge(parent::getProfileParams(), $params);
    }
}

<?php

namespace TueFind;

use Symfony\Component\Intl\Countries;

class Bootstrapper extends \VuFind\Bootstrapper
{
    protected function initViewModel(): void
    {
        $viewModel = $this->container->get('HttpViewManager')->getViewModel();
        $viewModel->setVariable('allCountries', Countries::getNames());
    }

}

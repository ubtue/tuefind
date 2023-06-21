<?php

namespace TueFind;

use VuFind\I18n\Locale\LocaleSettings;
use Symfony\Component\Intl\Countries;

class Bootstrapper extends \VuFind\Bootstrapper
{
    protected function initViewModel(): void
    {
        $settings = $this->container->get(LocaleSettings::class);
        $locale = $settings->getUserLocale();
        $viewModel = $this->container->get('HttpViewManager')->getViewModel();
        $viewModel->setVariable('userLang', $locale);
        $viewModel->setVariable('allLangs', $settings->getEnabledLocales());
        \Locale::setDefault($locale);
        $viewModel->setVariable('allCountries', Countries::getNames());
        $viewModel->setVariable('rtl', $settings->isRightToLeftLocale($locale));
    }

}

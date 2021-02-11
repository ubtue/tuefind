<?php

namespace TueFind;

use \Laminas\Mvc\MvcEvent;

/**
 * Bot protection
 *
 * We assume that a bot unconditionally tries to write to every found field.
 * So, if a hidden input field with a special name is not empty, then it must have been filled out by a bot.
 * The field exists in the HTML form, but is hidden by CSS.
 */
class BotProtect {

    const BOT_PROTECT_FIELD = 'botprotect';

    /**
     * Abort & display error page if botprotect field detected
     *
     * @param MvcEvent $event
     */
    static public function ProcessRequest(MvcEvent $event) {
        if ($event->getRequest()->getQuery(self::BOT_PROTECT_FIELD) != '') {
            $response = $event->getResponse();
            $response->setStatusCode(400);
            $response->setContent("bot detected");
            $response->send();
            exit;
        }
    }

    /**
     * Automatically add botprotect element to all forms
     *
     * @param MvcEvent $event
     */
    static public function ProcessResponse(MvcEvent $event) {
        $response = $event->getResponse();
        $content = preg_replace('"</form>"i', '<input name="'.self::BOT_PROTECT_FIELD.'" class="'.self::BOT_PROTECT_FIELD.'" type="text"></input></form>', $response->getContent());
        $response->setContent($content);
    }
}

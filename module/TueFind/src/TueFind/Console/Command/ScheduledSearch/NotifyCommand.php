<?php

namespace TueFind\Console\Command\ScheduledSearch;

use Symfony\Component\Console\Attribute\AsCommand;

use function count;
use function sprintf;

#[AsCommand(
    name: 'scheduledsearch/notify',
    description: 'Scheduled Search Notifier'
)]
class NotifyCommand extends \VuFindConsole\Command\ScheduledSearch\NotifyCommand
{
    /**
     * TueFind: Same as parent, but also pass $searchObject to sendEmail to modify subject
     */
    protected function processViewAlerts()
    {
        $todayTime = new \DateTime();
        $scheduled = $this->searchService->getScheduledSearches();
        $this->msg(sprintf('Processing %d searches', count($scheduled)));
        foreach ($scheduled as $s) {
            $lastTime = $s->getLastNotificationSent();
            if (
                !$this->validateSchedule($todayTime, $lastTime, $s)
                || !($user = $this->getUserForSearch($s))
                || !($searchObject = $this->getObjectForSearch($s))
                || !($newRecords = $this->getNewRecords($searchObject, $lastTime))
            ) {
                continue;
            }
            // Set email language
            $this->setLanguage($user->getLastLanguage());

            // Prepare email content
            $message = $this->buildEmail($s, $user, $searchObject, $newRecords);
            if (!$this->sendEmail($user, $message, $searchObject)) {
                // If email send failed, move on to the next user without updating
                // the database table.
                continue;
            }
            try {
                $s->setLastNotificationSent(new \DateTime());
                $this->searchService->persistEntity($s);
            } catch (\Exception) {
                $this->err("Error updating last_executed date for search {$s->getId()}");
            }
        }
        $this->msg('Done processing searches');
    }

    // TueFind: Add searchObject for subject
    protected function sendEmail($user, $message, ?\VuFind\Search\Base\Results $searchObject = null)
    {
        $subject = $this->mainConfig->Site->title
            . ': ' . $this->translate('Scheduled Alert Results');

        if (isset($searchObject)) {
            $subject .= ' (' . $searchObject->getParams()->getDisplayQuery() . ')';
        }

        $from = $this->getEmailSenderAddress($this->mainConfig);
        $to = $user->getEmail();
        try {
            $this->mailer->send($to, $from, $subject, $message);
            return true;
        } catch (\Exception $e) {
            $this->msg(
                'Initial email send failed; resetting connection and retrying...'
            );
        }
        // If we got this far, the first attempt threw an exception; let's reset
        // the connection, then try again....
        $this->mailer->resetConnection();
        try {
            $this->mailer->send($to, $from, $subject, $message);
        } catch (\Exception $e) {
            $this->err(
                "Failed to send message to {$user->getEmail()}: " . $e->getMessage()
            );
            return false;
        }
        // If we got here, the retry was a success!
        return true;
    }
}

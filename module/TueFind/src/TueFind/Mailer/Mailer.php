<?php

namespace TueFind\Mailer;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use VuFind\Exception\Mail as MailException;

class Mailer extends \VuFind\Mailer\Mailer
{
    protected string $siteAddress;
    protected string $siteTitle;

    /**
     * TueFind:
     * (- Override from via config: This was replaced by a standard feature in 11.0)
     * - Enable Spamfilter
     * - Add Footer
     */
    public function send(
        string|Address|array $to,
        string|Address $from,
        string $subject,
        string|Email $body,
        string|Address|array|null $cc = null,
        string|Address|array|null $replyTo = null,
        bool $subjectInBody = true,
        array $parts = [],
        bool $tuefindSpamfilter=false
    ) {
        try {
            if (!($from instanceof Address)) {
                $from = new Address($from);
            }
        } catch (RfcComplianceException $e) {
            throw new MailException('Invalid Sender Email Address', MailException::ERROR_INVALID_SENDER, $e);
        }
        try {
            $recipients = $this->convertToAddressList($to);
        } catch (RfcComplianceException $e) {
            throw new MailException('Invalid Recipient Email Address', MailException::ERROR_INVALID_RECIPIENT, $e);
        }
        try {
            $replyTo = $this->convertToAddressList($replyTo);
        } catch (RfcComplianceException $e) {
            throw new MailException('Invalid Reply-To Email Address', MailException::ERROR_INVALID_REPLY_TO, $e);
        }
        try {
            $cc = $this->convertToAddressList($cc);
        } catch (RfcComplianceException $e) {
            throw new MailException('Invalid CC Email Address', MailException::ERROR_INVALID_RECIPIENT, $e);
        }

        // Validate recipient email address count:
        if (count($recipients) == 0) {
            throw new MailException('Invalid Recipient Email Address', MailException::ERROR_INVALID_RECIPIENT);
        }
        if ($this->maxRecipients > 0) {
            if ($this->maxRecipients < count($recipients)) {
                throw new MailException(
                    'Too Many Email Recipients',
                    MailException::ERROR_TOO_MANY_RECIPIENTS
                );
            }
        }

        if (
            !empty($this->fromAddressOverride)
            && $this->fromAddressOverride != $from->getAddress()
        ) {
            // Add the original from address as the reply-to address unless
            // a reply-to address has been specified
            if (!$replyTo) {
                $replyTo[] = $from->getAddress();
            }
            $name = $from->getName();
            if (!$name) {
                [$fromPre] = explode('@', $from->getAddress());
                $name = $fromPre ? $fromPre : null;
            }
            $from = new Address($this->fromAddressOverride, $name);
        }

        try {
            // Send message
            if ($body instanceof Email) {
                $email = $body;
                if (null === $email->getSubject()) {
                    $email->subject($subject);
                }
            } else {
                if ($subjectInBody) {
                    // Extract any subject line at the beginning of the message body:
                    $body = preg_replace_callback(
                        '/^Subject: (.+)\n+/',
                        function ($matches) use (&$subject) {
                            $subject = $matches[1];
                            return '';
                        },
                        $body
                    );
                }
                $email = $this->getNewMessage();
                $email->text($body);
                $email->subject($subject);
            }

            // TueFind: Append footer
            $tmpBody = $email->getBody()->bodyToString();
            $footer = $this->translate('mail_footer_please_contact') . PHP_EOL . $this->siteAddress;
            $tmpBody .= PHP_EOL . '--' . PHP_EOL . $footer;
            $email->text($tmpBody);

            // TueFind: Add header for spamfilter
            if ($tuefindSpamfilter) {
                $headers = $email->getHeaders()->addTextHeader('X-TueFind-Spamfilter', 'enabled');
            }

            $email->addFrom($from);
            foreach ($recipients as $current) {
                $email->addTo($current);
            }
            foreach ($cc as $current) {
                $email->addCc($current);
            }
            foreach ($replyTo as $current) {
                $email->addReplyTo($current);
            }
            foreach ($parts as $part) {
                $email->addPart($part);
            }
            $this->getTransport()->send($email);
            if ($logFile = $this->options['message_log'] ?? null) {
                $format = $this->options['message_log_format'] ?? 'plain';
                $data = 'serialized' === $format
                    ? base64_encode(serialize($email)) . "\x1E" // Record Separator
                    : $email->toString() . "\n\n";
                file_put_contents($logFile, $data, FILE_APPEND);
            }
        } catch (\Exception $e) {
            $this->logError((string)$e);
            // Convert all exceptions thrown by mailer into MailException objects:
            throw new MailException($e->getMessage(), MailException::ERROR_UNKNOWN, $e);
        }
    }

    public function setSiteAddress(string $siteAddress)
    {
        $this->siteAddress = $siteAddress;
    }

    public function setSiteTitle(string $siteTitle)
    {
        $this->siteTitle = $siteTitle;
    }

    public function getDefaultLinkSubject()
    {
        return $this->translate('bulk_email_title', ['%%siteTitle%%' => $this->siteTitle]);
    }

    public function getDefaultRecordSubject($record)
    {
        return $this->translate('Library Catalog Record', [ '%%siteTitle%%' => $this->siteTitle ]) . ': '
            . $record->getBreadcrumb();
    }
}

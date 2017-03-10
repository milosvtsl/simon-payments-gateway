<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Mail;


use Merchant\Model\MerchantRow;
use System\Model\EmailTemplateRow;
use User\Model\UserRow;
use System\Config\SiteConfig;
use User\Session\SessionManager;

@define("SWIFTMAILER_DIR", dirname(dirname(dirname(__DIR__))) . '/support/swiftmailer/');
require_once SWIFTMAILER_DIR . 'lib/swift_required.php';

abstract class AbstractEmail
{
    const TITLE = null;
    const TEMPLATE_SUBJECT = null;
    const TEMPLATE_BODY = null;

    protected $subject;
    protected $body;

    protected $to = array();
    protected $from = array();
    protected $bcc = array();

    public function __construct() {
        $this->subject = static::TEMPLATE_SUBJECT;
        $this->body = static::TEMPLATE_BODY;

        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->from = array(
                SiteConfig::$EMAIL_FROM_ADDRESS => SiteConfig::$EMAIL_FROM_TITLE
            );
    }

    protected function processTemplate(Array $params, MerchantRow $Merchant=null) {
        // Query email template
        if($Merchant) {
            $class = get_class($this);
            $EmailTemplate = EmailTemplateRow::fetchAvailableTemplate($class, $Merchant->getID());
            if($EmailTemplate) {
                // Replace email template
                $this->body = $EmailTemplate->getBody();
                $this->subject = $EmailTemplate->getSubject();
            }
        }

        // Pre-process site constants
        self::processTemplateConstants($this->body, $this->subject, $Merchant);

        foreach($params as $name => $value) {
            $this->body = str_replace('{$' . $name . '}', $value, $this->body);
            $this->subject = str_replace('{$' . $name . '}', $value, $this->subject);
        }

        if(strpos($this->body, '{$')!==false) error_log("Not all variables were replaced: \n" . $this->body);
        if(strpos($this->subject, '{$')!==false) error_log("Not all variables were replaced: \n" . $this->subject);

    }

    public function send() {
        $Transport = \Swift_SmtpTransport::newInstance(SiteConfig::$EMAIL_SERVER_HOST, SiteConfig::$EMAIL_SERVER_PORT);
        $Transport->setUsername(SiteConfig::$EMAIL_USERNAME);
        $Transport->setPassword(SiteConfig::$EMAIL_PASSWORD);

        $Mailer = \Swift_Mailer::newInstance($Transport);

        $Message = \Swift_Message::newInstance($this->subject);
        if($this->from)
            $Message->setFrom($this->from);
        if($this->to)
            $Message->setTo($this->to);
        if($this->bcc)
            $Message->setBcc($this->bcc);


        $HTML = <<<HTML
<html>
    <body>
        {$this->body}
    </body>
</html>
HTML;
        $Text = strip_tags(
            preg_replace('/<br[^>]*>/i', "\r\n", $this->body)
        );
        $Message->setBody($HTML, 'text/html');
        $Message->addPart($Text, 'text/plain');

        return $Mailer->send($Message);
    }

    // Static

    static function processTemplateConstants(&$body, &$subject, MerchantRow $Merchant=null) {
        $constants = array(
            'SITE_NAME' => SiteConfig::$SITE_NAME,
            'SITE_URL_LOGO' => SiteConfig::$SITE_URL_LOGO,
            'SITE_URL_MERCHANT_LOGO' => SiteConfig::$SITE_URL_LOGO,
            'SITE_DEFAULT_MERCHANT_NAME' => SiteConfig::$SITE_DEFAULT_MERCHANT_NAME,
            'SITE_DEFAULT_CUSTOMER_NAME' => SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME,
        );
        if($Merchant && $Merchant->hasLogoPath())
            $constants['SITE_URL_MERCHANT_LOGO'] = $Merchant->getLogoPathURL();

        // Pre-process site constants
        foreach($constants as $name => $value) {
            $body = str_replace('{$' . $name . '}', $value, $body);
            $subject = str_replace('{$' . $name . '}', $value, $subject);
        }

    }
}


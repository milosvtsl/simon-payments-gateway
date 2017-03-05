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

@define("PHPMAILER_DIR", dirname(dirname(dirname(__DIR__))) . '/support/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

abstract class AbstractEmail extends \PHPMailer
{
    const TITLE = null;
    const BCC = null;
    const TEMPLATE_SUBJECT = null;
    const TEMPLATE_BODY = null;

    public function __construct() {
        parent::__construct();

        $this->Host = SiteConfig::$EMAIL_SERVER_HOST;
        $this->Username = SiteConfig::$EMAIL_USERNAME;
        $this->Password = SiteConfig::$EMAIL_PASSWORD;
        $this->Port = SiteConfig::$EMAIL_SERVER_PORT;
        $this->Timeout = SiteConfig::$EMAIL_TIMEOUT;
        $this->SMTPAuth = SiteConfig::$EMAIL_SMTP_AUTH;
        $this->SMTPSecure = SiteConfig::$EMAIL_SMTP_SECURE;
        if(SiteConfig::$EMAIL_SMTP_AUTH)
            $this->isSMTP();


        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

    }

    protected function processTemplate($body, $subject, $bcc, Array $params, MerchantRow $Merchant=null) {
        // Query email template
        if($Merchant) {
            $class = get_class($this);
            $EmailTemplate = EmailTemplateRow::fetchAvailableTemplate($class, $Merchant->getID());
            if($EmailTemplate) {
                // Replace email template
                $body = $EmailTemplate->getBody();
                $subject = $EmailTemplate->getSubject();
            }
        }

        // Pre-process site constants
        self::processTemplateConstants($body, $subject, $bcc);

        foreach($params as $name => $value) {
            $body = str_replace('{$' . $name . '}', $value, $body);
            $subject = str_replace('{$' . $name . '}', $value, $subject);
            $bcc = str_replace('{$' . $name . '}', $value, $bcc);
        }

        if(strpos($body, '{$')!==false) error_log("Not all variables were replaced: \n" . $body);
        if(strpos($subject, '{$')!==false) error_log("Not all variables were replaced: \n" . $subject);
        if(strpos($bcc, '{$')!==false) error_log("Not all variables were replaced: \n" . $bcc);


        $this->addBCC($bcc);
        $this->Subject = $subject;

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        {$body}
    </body>
</html>
HTML;

        $this->AltBody = strip_tags(
            preg_replace('/<br[^>]*>/i', "\r\n", $body)
        );
    }

    static function processTemplateConstants(&$body, &$subject, &$bcc, MerchantRow $Merchant=null) {
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
            $bcc = str_replace('{$' . $name . '}', $value, $bcc);
        }

    }
}


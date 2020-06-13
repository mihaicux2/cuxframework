<?php

/**
 * CuxMailer class file
 * 
 * @package Components
 * @subpackage Mail
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\mail;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Simple PHPMailer wrapper that can be used to send emails
 */
class CuxMailer extends CuxBaseObject {
    
    /**
     * Send mail FROM this address
     * @var string
     */
    public $from;
    
    /**
     * REPLY TO this address
     * @var string
     */
    public $replyTo = "";
    
    /**
     * Send mails connecting to a "to-be-defined" SMTP server
     * @var bool 
     */
    public $smtp = false;
    
    /**
     * SMTP server host
     * @var string
     */
    public $host = "";
    
    /**
     * SMTP sever port
     * @var int
     */
    public $port;
    
    /**
     * Is the SMTP connection SSL secured
     * @var bool
     */
    public $smtpSecure = false;
    
    /**
     * SMTP connection username
     * @var string
     */
    public $username = "";
    
    /**
     * SMTP connection password
     * @var string
     */
    public $password = "";
    
    /**
     * The list of mail recipients
     * @var arary
     */
    private $_to = array();
    
    /**
     * The list of carbon copy mail recipients
     * @var array
     */
    private $_cc = array();
    
    /**
     * The list of blind carbon copy mail recipients
     * @var array 
     */
    private $_bcc = array();
    
    /**
     * The subject of the mail to be sent
     * @var string
     */
    private $_subject = "";
    
    /**
     * The content of the mail to be sent
     * @var string
     */
    private $_body = "";
    
    /**
     * The list of headers to be used while sending the mail
     * @var string
     */
    private $_headers = array();
    
    /**
     * The PHPMailer object instance
     * @var PHPMailer\PHPMailer\PHPMailer 
     */
    private $_mail;
    
    /**
     * Setup the class instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
//        require("vendor/PHPMailer/PHPMailerAutoload.php");

        $this->_mail = new PHPMailer;
        
        $this->_mail->Host = $this->host;
        if ($this->smtp) {
            $this->_mail->isSMTP();
            $this->_mail->SMTPAuth = true;
        }
        $this->_mail->Username = $this->username;
        $this->_mail->Password = $this->password;
        $this->_mail->SMTPSecure = $this->smtpSecure;
        $this->_mail->Port = $this->port;
        
        $this->setFrom($this->from);
    }
    
    /**
     * Add recipient(s) for the email
     * ie: "Mihail Cuculici <mihai.cuculici@gmail.com>"
     * @param string|array $to The email address(es) that will receive the email
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function addTo($to = array()): CuxMailer{
        if (is_array($to) && !empty($to)){
            $this->_to = array_merge($this->_to, $to);
            foreach ($to as $email){
                if (($pos = strpos($email, "<")) != false){
                    $emailName = trim(substr($email, 0, $pos));
                    $emailAddress = trim(substr($email, $pos+1, -1));
                    $this->_mail->addAddress($emailAddress, $emailName);
                }
                else{
                    $this->_mail->addAddress($email);
                }
            }
        }
        elseif (is_string($to) && !empty($to)){
            $this->_to[] = $to;
            $email = $to;
            if (($pos = strpos($email, "<")) != false){
                $emailName = trim(substr($email, 0, $pos));
                $emailAddress = trim(substr($email, $pos+1, -1));
                $this->_mail->addAddress($emailAddress, $emailName);
            }
            else{
                $this->_mail->addAddress($email);
            }
        }
        return $this;
    }
    
    /**
     * Send emails on behalf of (this value)
     * @param string $from The email address that is used for sending the mail
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function setFrom($from): CuxMailer{

        $this->from = $from;
        $email = $from;
        if (($pos = strpos($email, "<")) != false){
            $emailName = trim(substr($email, 0, $pos));
            $emailAddress = trim(substr($email, $pos+1, -1));
            $this->_mail->setFrom($emailAddress, $emailName);
        }
        else{
            $this->_mail->setFrom($email);
        }
        return $this;
    }
    
    /**
     * Add Carbon Copy recipient(s) for the email
     * @param string|array $cc The email address(es) that will receive the email
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function addCC($cc = array()): CuxMailer{
        if (is_array($cc) && !empty($cc)){
            $this->_cc = array_merge($this->_cc, $cc);
            foreach ($cc as $email){
                if (($pos = strpos($email, "<")) != false){
                    $emailName = trim(substr($email, 0, $pos));
                    $emailAddress = trim(substr($email, $pos+1, -1));
                    $this->_mail->addCC($emailAddress, $emailName);
                }
                else{
                    $this->_mail->addCC($email);
                }
            }
        }
        elseif (is_string($cc) && !empty($cc)){
            $this->_cc[] = $cc;
            $email = $cc;
            if (($pos = strpos($email, "<")) != false){
                $emailName = trim(substr($email, 0, $pos));
                $emailAddress = trim(substr($email, $pos+1, -1));
                $this->_mail->addCC($emailAddress, $emailName);
            }
            else{
                $this->_mail->addCC($email);
            }
        }
        return $this;
    }
    
    /**
     * Add Blind Carbon Copy recipient(s) for the email
     * @param string|array $bcc The email address(es) that will receive the email
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function addBCC($bcc = array()): CuxMailer{
        if (is_array($bcc) && !empty($bcc)){
            $this->_bcc = array_merge($this->_cc, $bcc);
            foreach ($bcc as $email){
                if (($pos = strpos($email, "<")) != false){
                    $emailName = trim(substr($email, 0, $pos));
                    $emailAddress = trim(substr($email, $pos+1, -1));
                    $this->_mail->addBCC($emailAddress, $emailName);
                }
                else{
                    $this->_mail->addBCC($email);
                }
            }
        }
        elseif (is_string($bcc) && !empty($bcc)){
            $this->_bcc[] = $bcc;
            $email = $bcc;
            if (($pos = strpos($email, "<")) != false){
                $emailName = trim(substr($email, 0, $pos));
                $emailAddress = trim(substr($email, $pos+1, -1));
                $this->_mail->addBCC($emailAddress, $emailName);
            }
            else{
                $this->_mail->addBCC($email);
            }
        }
        return $this;
    }
    
    /**
     * Set the mail subject
     * @param string $subject The subject of the mail to be sent
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function setSubject(string $subject): CuxMailer{
        $this->_subject = $subject;
        $this->_mail->Subject = $subject;
        return $this;
    }
    
    /**
     * Set the mail content
     * @param string $body The content of the mail to be sent
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function setBody($body): CuxMailer{
        $this->_body = $body;
        $this->_mail->Body = $body;
        $this->_mail->AltBody = strip_tags(str_ireplace(array("<br>", "<br/>", "<br />"), "\r\n", $body));
        return $this;
    }
    
    /**
     * Adds header information to the mail to be sent
     * @param string $header Header details
     * @return \CuxFramework\components\mail\CuxMailer
     */
    public function addHeader($header): CuxMailer{
        $this->_headers[] = $header;
        return $this;
    }
    
    /**
     * Build and format all the headers needed for the mail to be sent
     * @return string
     */
    public function getFormatedHeaders(): string{
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        if (!empty($this->_to)){
            $headers .= 'To: ' . implode(", ", $this->_to) . "\r\n";
        }
        if (!empty($this->from)){
            $headers .= 'From: ' . $this->from . "\r\n";
        }
        if (!empty($this->replyTo)){
            $headers .= 'Reply-To: ' . $this->replyTo . "\r\n";
        }
        if (!empty($this->_cc)){
            $headers .= 'Cc: ' . implode(", ", $this->_cc) . "\r\n";
        }
        if (!empty($this->_bcc)){
            $headers .= 'Bcc: ' . implode(", ", $this->_bcc) . "\r\n";
        }
        $headers .= 'X-Mailer: PHP/' . phpversion()."\r\n";
        if (!empty($this->_headers)){
            $headers .= implode("\r\n", $this->_headers)."\r\n";
        }
        
        return $headers;
    }
    
    /**
     *  Send the mail to the recipient/list of recipients
     * @return bool True if the mail has been sent successfully
     */
    public function send(): bool{
        
        return ($this->smtp) ? $this->_mail->send() : mail(implode(", ", $this->_to), $this->_subject, $this->_body, $this->getFormatedHeaders());

    }
    
}
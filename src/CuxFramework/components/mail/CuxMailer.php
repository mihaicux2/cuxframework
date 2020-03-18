<?php

namespace CuxFramework\components\mail;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;
use PHPMailer\PHPMailer\PHPMailer;

class CuxMailer extends CuxBaseObject {
    
    public $from;
    public $replyTo = false;
    public $smtp = false;
    public $host = false;
    public $port = false;
    public $smtpSecure = false;
    public $username = "";
    public $password = "";
    
    private $_to = array();
    private $_cc = array();
    private $_bcc = array();
    
    private $_subject = false;
    private $_body = false;
    
    private $_headers = array();
    
    private $_mail;
    
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
    
    public function setSubject(string $subject): CuxMailer{
        $this->_subject = $subject;
        $this->_mail->Subject = $subject;
        return $this;
    }
    
    public function setBody($body): CuxMailer{
        $this->_body = $body;
        $this->_mail->Body = $body;
        $this->_mail->AltBody = strip_tags(str_ireplace(array("<br>", "<br/>", "<br />"), "\r\n", $body));
        return $this;
    }
    
    public function addHeader($header): CuxMailer{
        $this->_headers[] = $header;
        return $this;
    }
    
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
    
    public function send(): bool{
        
        return ($this->smtp) ? $this->_mail->send() : mail(implode(", ", $this->_to), $this->_subject, $this->_body, $this->getFormatedHeaders());

    }
    
}
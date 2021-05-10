<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Description of class-send-mails
 *
 * @author adrien
 */
class Send_Mails {

    private $to;
    private $subject;
    private $body;
    private $headers;

    function __construct() {
        $this->headers = array('Content-Type: text/html; charset=UTF-8');
    }

    /**
     * @param Payzen_Subscribers_Plan $plan
     */
    public function generate_mail(Payzen_Subscribers_Plan $plan) {
        $this->to = $plan->getEmailSender();
        $this->subject = wp_unslash($plan->getEmailSubject());
        $this->body = wp_unslash($plan->getEmailMessage());
    }

    public function send_mail(): bool
    {
        // send message
        return wp_mail($this->to, $this->subject, $this->body, $this->headers);
    }

}

<?php

/**
 * Author: NguyenBa <banv.drei@gmail.com>
 * Last Edited: 01 September 2020
 * Edited By: NguyenBa
 */

add_action('phpmailer_init', 'custom_mail_smtp_server');
function custom_mail_smtp_server($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->IsHTML(true);
    $phpmailer->Host = 'smtp.mandrillapp.com';
    $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
    $phpmailer->Port = 25;
    $phpmailer->Username = 'info@twinger.vn';
    $phpmailer->Password = '3qk-Zw1DBJI5n6AC_MfcuA';
    $phpmailer->SMTPSecure = "tls"; // Choose SSL or TLS, if necessary for your server
    $phpmailer->From = 'info@twinger.vn';
    $phpmailer->FromName = 'Twinger';
    $phpmailer->setfrom("info@twinger.vn", "Twinger");
    //$phpmailer->SMTPDebug  = 1;
}

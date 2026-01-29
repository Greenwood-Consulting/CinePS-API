<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$transport = Transport::fromDsn($_ENV['MAILER_DSN']);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('contact@ton-domaine.fr')
    ->to('toi@ton-domaine.fr')
    ->subject('Test SMTP')
    ->text('SMTP OK');

$mailer->send($email);



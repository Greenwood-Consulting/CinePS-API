<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;


class MailController extends AbstractController
{

    // just a mail POC
    #[Route('/api/sendmail', name: 'sendmail', methods: ['GET'])]
    public function sendmail(): JsonResponse
    {

        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('XXX@XXX.com')
            ->to('YYY@gYYY.com')
            ->subject('Test SMTP')
            ->text('this is a POC using Symfony\Component\Mailer');

        $mailer->send($email);

        return new JsonResponse("ok", Response::HTTP_OK, [], true);
    }
 
}

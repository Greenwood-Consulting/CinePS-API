<?php
namespace Tests\Controller\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

class LoginCheckTest extends TestCase
{
    /**
     * Set up the environment for the tests.
     */
    protected function setUp(): void
    {
        // Charger les variables d'environnement si nécessaire
        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv = new Dotenv();
            $dotenv->load(__DIR__ . '/../../.env');
        }
    }

    public function testLoginCheckReturnsToken()
    {
        $client = HttpClient::create();

        $response = $client->request('POST', $_ENV['API_BASE_URL'].'/api/login_check', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'email' => $_ENV['API_EMAIL'],
                'password' => $_ENV['API_PASSWORD'],
            ],
        ]);

        // Vérifie que le code HTTP est 200
        $this->assertSame(200, $response->getStatusCode());

        // Récupère et parse le JSON
        $data = $response->toArray();

        // Vérifie que la réponse contient un token
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }
}

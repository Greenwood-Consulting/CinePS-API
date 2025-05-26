<?php

namespace Tests\Controller\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrentSemaineTest extends TestCase
{
    private HttpClientInterface $client;
    private string $token = '';

    protected function setUp(): void
    {
        // Charger les variables d'environnement si nécessaire
        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv = new Dotenv();
            $dotenv->load(__DIR__ . '/../../.env');
        }
        $this->client = HttpClient::create();
    }

    public function testLoginAndCallCurrentSemaine(): void
    {
        $this->authenticate();
        $this->callCurrentSemaine();
    }

    private function authenticate(): void
    {
        $response = $this->client->request('POST', $_ENV['API_BASE_URL'].'/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $_ENV['API_EMAIL'],
                'password' => $_ENV['API_PASSWORD'],
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode(), 'Login should return 200');

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data, 'Token should be present in login response');

        $this->token = $data['token'];
        $this->assertNotEmpty($this->token, 'Token should not be empty');
    }

    private function callCurrentSemaine(): void
    {
        $response = $this->client->request('GET', $_ENV['API_BASE_URL'].'/api/currentSemaine', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode(), 'GET /api/currentSemaine should return 200');

        $data = $response->toArray();
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertGreaterThan(0, count($data), 'Response should contain at least one item');
        $this->assertArrayHasKey('id', $data[0], 'Each item should have an id');
    }
}
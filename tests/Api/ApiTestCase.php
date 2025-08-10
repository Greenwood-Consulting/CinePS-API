<?php
namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class ApiTestCase extends TestCase
{
    protected HttpClientInterface $client;
    private static bool $envBooted = false;
    private static ?string $cachedToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootEnvOnce();

        // Base URI option pratique pour éviter de répéter le host
        $this->client = HttpClient::createForBaseUri(
            rtrim($_ENV['API_BASE_URL'], '/'),
            ['headers' => ['Accept' => 'application/json']]
        );
    }

    private function bootEnvOnce(): void
    {
        if (self::$envBooted) {
            return;
        }
        // Charge .env(.test) si besoin
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (is_file($envPath)) {
            (new Dotenv())->usePutenv()->loadEnv($envPath);
        }
        self::$envBooted = true;
    }

    protected function getToken(): string
    {
        if (self::$cachedToken) {
            return self::$cachedToken;
        }

    // Debug temporaire : afficher les variables utiles
    fwrite(STDERR, "API_BASE_URL=" . ($_ENV['API_BASE_URL'] ?? 'NON DEFINI') . PHP_EOL);
    fwrite(STDERR, "API_EMAIL=" . ($_ENV['API_EMAIL'] ?? 'NON DEFINI') . PHP_EOL);
    fwrite(STDERR, "API_PASSWORD=" . ($_ENV['API_PASSWORD'] ?? 'NON DEFINI') . PHP_EOL);

        // Adapte l’endpoint et le payload à ton API
        $resp = $this->client->request('POST', '/api/login_check', [
            'json' => [
                'email' => $_ENV['API_EMAIL'] ?? 'a@a.fr',
                'password' => $_ENV['API_PASSWORD'] ?? 'password',
            ],
        ]);

        $data = $resp->toArray(false);
        // Clé typique avec LexikJWT: "token"
        self::$cachedToken = $data['token'] ?? ($data['id_token'] ?? '');
        if (!self::$cachedToken) {
            $this->fail('Impossible de récupérer le token JWT pour les tests.');
        }
        return self::$cachedToken;
    }

    protected function authHeaders(array $extra = []): array
    {
        return array_merge(['Authorization' => 'Bearer ' . $this->getToken()], $extra);
    }

    // Petits helpers pour éviter de répéter les verbes HTTP
    protected function apiGet(string $uri, array $options = [])
    {
        $options['headers'] = ($options['headers'] ?? []) + $this->authHeaders();
        return $this->client->request('GET', $uri, $options);
    }

    protected function apiPost(string $uri, array $options = [])
    {
        $options['headers'] = ($options['headers'] ?? []) + $this->authHeaders();
        return $this->client->request('POST', $uri, $options);
    }

    protected function apiPatch(string $uri, array $options = [])
    {
        $options['headers'] = ($options['headers'] ?? []) + $this->authHeaders();
        return $this->client->request('PATCH', $uri, $options);
    }

    protected function apiPut(string $uri, array $options = [])
    {
        $options['headers'] = ($options['headers'] ?? []) + $this->authHeaders();
        return $this->client->request('PUT', $uri, $options);
    }

    protected function apiDelete(string $uri, array $options = [])
    {
        $options['headers'] = ($options['headers'] ?? []) + $this->authHeaders();
        return $this->client->request('DELETE', $uri, $options);
    }
}

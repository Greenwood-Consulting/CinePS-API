<?php

namespace Tests\Controller\Api;

use Tests\Api\ApiTestCase;

class IndexConnexionTest extends ApiTestCase
{
    public function testIndexConnexion(): void
    {
        $this->callOneMembre();
    }

    private function callOneMembre(): void
    {
        $r = $this->apiGet('/api/membres/1');
        $this->assertSame(200, $r->getStatusCode(), 'GET /api/membres/1 should return 200');
        $data = $r->toArray();
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertArrayHasKey('id', $data, 'Response should have an id');
    }
    // @TODO: ajouter le test de GET /api/filmsGagnants
}
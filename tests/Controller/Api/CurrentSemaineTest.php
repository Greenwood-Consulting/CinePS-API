<?php

namespace Tests\Controller\Api;

use Tests\Api\ApiTestCase;

class CurrentSemaineTest extends ApiTestCase
{
    public function testCurrentSemaineReturns200(): void
    {
        $r = $this->apiGet('/api/currentSemaine');
        $this->assertSame(200, $r->getStatusCode(), 'GET /api/currentSemaine should return 200');

        $data = $r->toArray(false);
        $this->assertIsObject((object)$data, 'Response should be an object');
        $this->assertArrayHasKey('id', $data);
    }
}
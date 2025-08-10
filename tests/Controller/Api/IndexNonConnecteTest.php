<?php

namespace Tests\Controller\Api;

use Tests\Api\ApiTestCase;

class IndexNonConnecteTest extends ApiTestCase
{

    private int $currentSemaine_id = 0;
    
    public function testIndexNonConnecte(): void
    {
        $this->callCurrentSemaine();
        $this->callMembres();
        $this->callIsVoteTermine();
        $this->callNextProposeurs();
    }

    private function callCurrentSemaine(): void
    {
        $r = $this->apiGet('/api/currentSemaine');

        $this->assertSame(200, $r->getStatusCode(), 'GET /api/currentSemaine should return 200');

        $data = $r->toArray();
        $this->assertIsObject((object)$data, 'Response should be an object');
        $this->assertArrayHasKey('id', $data, 'Object should have an id');

        $this->currentSemaine_id = $data['id'];

    }

    private function callMembres(): void
    {
        $r = $this->apiGet('/api/membres');

        $this->assertSame(200, $r->getStatusCode(), 'GET /api/membres should return 200');

        $data = $r->toArray();
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertGreaterThan(0, count($data), 'Response should contain at least one item');
        $this->assertArrayHasKey('id', $data[0], 'Each item should have an id');
    }

    private function callIsVoteTermine(): void
    {
        $r = $this->apiGet('/api/isVoteTermine/'.$this->currentSemaine_id);
        $this->assertSame(200, $r->getStatusCode(), 'GET /api/isVoteTermine should return 200');
        $data = filter_var($r->getContent(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->assertNotNull($data, 'Response should be a valid boolean');
        $this->assertIsBool($data, 'Response should be a boolean');
    }

    private function callNextProposeurs(): void
    {
        $r = $this->apiGet('/api/nextProposeurs/'. $this->currentSemaine_id);
        $this->assertSame(200, $r->getStatusCode(), 'GET /api/nextProposeurs should return 200');
        $data = $r->toArray();
        $this->assertIsArray($data, 'Response should be an array');
    }
}
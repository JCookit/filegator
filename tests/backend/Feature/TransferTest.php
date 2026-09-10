<?php

namespace Tests\Feature;

use Tests\TestCase;

class TransferTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('FILEGATOR_TRANSFER_STATUS');
    }

    public function testStatusIsAdminOnly()
    {
        $this->signOut();
        $this->sendRequest('GET', '/transferstatus');
        $this->assertStatus(404);
        $this->signIn('john@example.com', 'john123');
        $this->sendRequest('GET', '/transferstatus');
        $this->assertStatus(404);
    }

    public function testMissingAndStaleStatusAreUnavailable()
    {
        $this->signIn('admin@example.com', 'admin123');
        putenv('FILEGATOR_TRANSFER_STATUS=/nonexistent/status.json');
        $this->sendRequest('GET', '/transferstatus');
        $this->assertResponseJsonHas(['data' => ['enabled' => true, 'available' => false]]);

        $path = tempnam(sys_get_temp_dir(), 'transfer-test-');
        try {
            putenv('FILEGATOR_TRANSFER_STATUS='.$path);
            file_put_contents($path, json_encode(['updated' => time() - 60, 'available' => true]));
            $this->sendRequest('GET', '/transferstatus');
            $this->assertResponseJsonHas(['data' => ['enabled' => true, 'available' => false]]);
            file_put_contents($path, json_encode(['updated' => time(), 'available' => true, 'queued' => 3]));
            $this->sendRequest('GET', '/transferstatus');
            $this->assertStatus(200);
            $this->assertResponseJsonHas(['data' => ['enabled' => true, 'available' => true, 'queued' => 3]]);
        } finally {
            unlink($path);
        }
    }
}

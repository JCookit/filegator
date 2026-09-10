<?php

namespace Filegator\Controllers;

use Filegator\Kernel\Response;

class TransferController
{
    public function status(Response $response)
    {
        $path = getenv('FILEGATOR_TRANSFER_STATUS');
        if (! $path) {
            return $response->json(['enabled' => false]);
        }

        $status = json_decode(@file_get_contents($path), true);
        if (! is_array($status) || empty($status['updated']) || time() - $status['updated'] > 45) {
            return $response->json(['enabled' => true, 'available' => false]);
        }

        $status['enabled'] = true;

        return $response->json($status);
    }
}

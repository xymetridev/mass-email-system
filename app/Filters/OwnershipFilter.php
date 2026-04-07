<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class OwnershipFilter implements FilterInterface
{
    /**
     * Enforces user ownership checks for routes containing an owner id segment.
     *
     * Expected usage:
     * - Route includes numeric owner id in segment 2 by default, e.g. /users/{id}/...
     * - Optional arg allows segment override, e.g. 'ownership:3'
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $segment = isset($arguments[0]) ? (int) $arguments[0] : 2;
        $ownerId = (int) $request->uri->getSegment($segment);

        if ($ownerId <= 0 || ! can_access_owner($ownerId)) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status'  => 403,
                    'error'   => 'Forbidden',
                    'message' => 'You are not allowed to access this resource.',
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}

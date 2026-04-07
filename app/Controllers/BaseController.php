<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * @var array
     */
    protected $helpers = ['access'];

    public function initController(IncomingRequest $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function requireOwnership(int $ownerId): void
    {
        if (! can_access_owner($ownerId)) {
            throw \CodeIgniter\Exceptions\PageForbiddenException::forPageForbidden();
        }
    }

    protected function currentUserIdOrFail(): int
    {
        $userId = current_user_id();

        if ($userId === null) {
            throw \CodeIgniter\Exceptions\PageForbiddenException::forPageForbidden();
        }

        return $userId;
    }
}

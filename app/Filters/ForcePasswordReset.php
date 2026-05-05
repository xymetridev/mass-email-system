<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForcePasswordReset implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (auth()->loggedIn()) {
            $user = auth()->user();
            
            // Check if user is forced to reset password
            if ($user->requiresPasswordReset()) {
                // If they are not already on the set-password or logout page, redirect them
                $currentUri = (string) current_url(true)->setQuery('');
                
                if (strpos($currentUri, '/set-password') === false && strpos($currentUri, '/logout') === false) {
                    return redirect()->to(url_to('set-password-view'));
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}

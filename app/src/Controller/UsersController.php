<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\IdentityInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Cake\View\JsonView;
use Firebase\JWT\JWT;
use josegonzalez\Dotenv\Loader;
use Migrations\Command\Phinx\Dump;

/**
 * Users Controller
 *
 */
class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login']);
    }

    public function login()
    {
        $this->request->allowMethod(['post']);
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {

            $user = $result->getData();

            // Generate JWT token using secret key from environment variable
            $jwtPayload = [
                'user_id' => $user->id,
                'email' => $user->email,
            ];
            $token = JWT::encode($jwtPayload, env('JWT_SECRET_KEY', 'icv_secret_key'), 'HS256');
    
            $this->set([
                'token' => $token,
                'user' => $user,
            ]);
        } else {
            $this->response = $this->response->withStatus(401); // Unauthorized status code
            $this->set([
                'error' => 'Invalid username or password',
            ]);
        }
        $this->viewBuilder()->setOption('serialize', ['token', 'user']);
    }

    public function logout()
    {
        try {
            $this->request->allowMethod(['post']);
            $result = $this->Authentication->getResult();

            if ($result && $result->isValid()) {
                $this->Authentication->logout();
                $session = $this->request->getSession();
                $session->destroy();
            }
            
            $this->set([
                'message' => 'Logged out successfully',
            ]);
        }
        catch (UnauthorizedException $exception) {
            $this->response = $this->response->withStatus(401);
            $this->set('response', [
                'message' => 'Unauthorized',
                'error' => $exception->getMessage() .  '23232'
            ]);
            $this->set('_serialize', 'response');
        }
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}

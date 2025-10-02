<?php
namespace App\Controller\Api;

use App\Application\UseCase\CreateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private CreateUser $createUser;

    public function __construct(CreateUser $createUser)
    {
        $this->createUser = $createUser;
    }

    #[Route('/register', name: 'api_user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $roles = $data['roles'] ?? null;

        if (!$username || !$password) {
            return $this->json(['error' => 'Faltan datos requeridos.'], 400);
        }

        try {
            $user = $this->createUser->execute($username, $password, $roles);
            return $this->json([
                'message' => 'Usuario creado correctamente',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'roles' => $user->getRoles(),
                ]
            ], 201);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'error' => 'El nombre de usuario ya está en uso.'
            ], 400);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ocurrió un error: ' . $e->getMessage()
            ], 500);
        }
    }
}
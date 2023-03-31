<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route(path: "/api/v1/register", methods: "POST")]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): JsonResponse
    {
        $params = json_decode($request->getContent());

        $user = new User();
        $hashedPassword = $hasher->hashPassword($user, $params->password);
        $user->setEmail($params->email);
        $user->setUsername($params->email);
        $user->setPassword($hashedPassword);

        $userRepository->save($user, true);

        return $this->jsonResponse("User created", [
            'email' => (string)$user->getEmail()
        ], 201);
    }

    private function jsonResponse(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return $this->json([
            "statusCode" => $statusCode,
            "message" => $message,
            "data" => $data
        ], $statusCode);
    }
}
<?php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Application\UseCase\GetTrainerTeam;
use App\Application\UseCase\AssignPokemonToTrainer;

#[Route('/api/trainers')]
class TrainerController extends AbstractController
{
    private GetTrainerTeam $getTrainerTeam;
    private AssignPokemonToTrainer $assignPokemonToTrainer;

    public function __construct(
        GetTrainerTeam $getTrainerTeam,
        AssignPokemonToTrainer $assignPokemonToTrainer
    ) {
        $this->getTrainerTeam = $getTrainerTeam;
        $this->assignPokemonToTrainer = $assignPokemonToTrainer;
    }

    #[Route('/{id}/team', name: 'api_trainer_team', methods: ['GET'])]
    public function getTeam(int $id): JsonResponse
    {
        $data = $this->getTrainerTeam->execute($id);

        if (!$data) {
            return $this->json(['message' => 'Entrenador no encontrado'], 404);
        }

        return $this->json($data);
    }

    #[Route('/{trainerId}/team/{pokemonId}', name: 'api_assign_pokemon', methods: ['POST'])]
    public function assignPokemon(int $trainerId, int $pokemonId): JsonResponse
    {
        $this->assignPokemonToTrainer->execute($trainerId, $pokemonId);

        return $this->json([
            'message' => 'Pokémon asignado correctamente.'
        ]);
    }
}
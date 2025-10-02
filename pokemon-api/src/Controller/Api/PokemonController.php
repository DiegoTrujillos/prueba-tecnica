<?php
namespace App\Controller\Api;

use App\Application\UseCase\GetRandomPokemon;
use App\Application\UseCase\AssignMoveToPokemon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pokemon')]
class PokemonController extends AbstractController
{
    private GetRandomPokemon $getRandomPokemon;

    public function __construct(GetRandomPokemon $getRandomPokemon)
    {
        $this->getRandomPokemon = $getRandomPokemon;
    }

    #[Route('/random', name: 'api_pokemon_random', methods: ['GET'])]
    public function random(): JsonResponse
    {
        $pokemon = $this->getRandomPokemon->execute();
        if (!$pokemon) {
            return $this->json(['message' => 'No hay Pokémon salvajes'], 404);
        }
        return $this->json($pokemon);
    }

    #[Route('/{pokemonId}/moves', name: 'api_pokemon_learn_move', methods: ['POST'])]
    public function learnMove(
        int $pokemonId,
        Request $request,
        AssignMoveToPokemon $assignMoveToPokemon
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $moveId = $data['move_id'] ?? null;

        if (!$moveId) {
            return $this->json(['error' => 'Se requiere move_id'], 400);
        }

        try {
            $pokemon = $assignMoveToPokemon->execute($pokemonId, $moveId);

            return $this->json([
                'message' => '¡El movimiento ha sido aprendido con éxito!',
                'pokemon' => [
                    'id' => $pokemon->getId(),
                    'name' => $pokemon->getName(),
                    'nickname' => $pokemon->getNickname(),
                    'level' => $pokemon->getLevel(),
                    'moves' => array_map(fn($move) => [
                        'id' => $move->getId(),
                        'name' => $move->getName()
                    ], $pokemon->getMoves()->toArray())
                ]
            ]);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
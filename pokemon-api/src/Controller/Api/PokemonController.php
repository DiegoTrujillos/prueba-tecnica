<?php
namespace App\Controller\Api;

use App\Application\UseCase\GetRandomPokemon;
use App\Application\UseCase\AssignMoveToPokemon;
use App\Application\UseCase\RemoveMoveFromPokemon;
use App\Application\UseCase\AssignTypeToPokemon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
    #[Route('/{pokemonId}/moves/{moveId}', name: 'api_pokemon_forget_move', methods: ['DELETE'])]
    public function forgetMove(
        int $pokemonId,
        int $moveId,
        RemoveMoveFromPokemon $removeMoveFromPokemon
    ): JsonResponse {
        try {
            $pokemon = $removeMoveFromPokemon->execute($pokemonId, $moveId);

            return $this->json([
                'message' => 'El movimiento ha sido olvidado.',
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
        } catch (NotFoundHttpException | AccessDeniedHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
    #[Route('/{pokemonId}/assign-type', name: 'pokemon_assign_type', methods: ['POST'])]
    public function assignType(
        Request $request,
        int $pokemonId,
        AssignTypeToPokemon $AssignTypeToPokemon
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $typeId = $data['typeId'] ?? null;

        if (!$typeId) {
            return $this->json(['error' => 'Se requiere el ID del tipo.'], 400);
        }

        try {
            $pokemon = $AssignTypeToPokemon->execute( (int) $pokemonId, (int) $typeId);

            return $this->json([
                'message' => 'Tipo asignado correctamente',
                'pokemon' => [
                    'id' => $pokemon->getId(),
                    'name' => $pokemon->getName(),
                    'types' => array_map(fn($t) => $t->getName(), $pokemon->getTypes()->toArray())
                ]
            ], 200);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
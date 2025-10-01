<?php
namespace App\Controller\Api;

use App\Application\UseCase\GetRandomPokemon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}

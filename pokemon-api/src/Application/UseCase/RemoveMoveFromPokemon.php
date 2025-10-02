<?php
namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\MoveRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RemoveMoveFromPokemon
{
    private PokemonRepositoryInterface $pokemonRepository;
    private MoveRepositoryInterface $moveRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        PokemonRepositoryInterface $pokemonRepository,
        MoveRepositoryInterface $moveRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->pokemonRepository = $pokemonRepository;
        $this->moveRepository = $moveRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function execute(mixed $pokemonId, mixed $moveId)
    {
        if (!is_numeric($pokemonId) || !is_numeric($moveId)) {
            throw new \InvalidArgumentException('Los IDs de Pokémon y Movimiento deben ser números.');
        }
        $pokemonId = (int)$pokemonId;
        $moveId = (int)$moveId;

        $token = $this->tokenStorage->getToken();
        /** @var User|null $currentUser */
        $currentUser = $token ? $token->getUser() : null;

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Usuario no autenticado.');
        }

        $pokemon = $this->pokemonRepository->findById($pokemonId);
        if (!$pokemon) {
            throw new NotFoundHttpException('Pokémon no encontrado.');
        }

        if ($pokemon->getTrainer() === null || $pokemon->getTrainer()->getId() !== $currentUser->getId()) {
            throw new AccessDeniedHttpException('No tienes permiso para eliminar movimientos de este Pokémon.');
        }

        $move = $this->moveRepository->findById($moveId);
        if (!$move) {
            throw new NotFoundHttpException('Movimiento no encontrado.');
        }

        if ($pokemon->getMoves()->contains($move)) {
            $pokemon->removeMove($move);
            $this->pokemonRepository->save($pokemon);
        }

        return $pokemon;
    }
}
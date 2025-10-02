<?php
namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\MoveRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssignMoveToPokemon
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

    public function execute(int $pokemonId, int $moveId)
    {
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
            throw new AccessDeniedHttpException('No tienes permiso para enseñar movimientos a este Pokémon.');
        }

        $move = $this->moveRepository->findById($moveId);
        if (!$move) {
            throw new NotFoundHttpException('Movimiento no encontrado.');
        }

       $pokemonTypeIds = [];
        foreach ($pokemon->getTypes() as $type) {
            $pokemonTypeIds[] = $type->getId();
        }

        $moveTypeId = $move->getType()->getId();

        if (!in_array($moveTypeId, $pokemonTypeIds, true)) {
            throw new \DomainException('El movimiento no es compatible con los tipos de este Pokémon.');
        }

        $pokemon->addMove($move);
        $this->pokemonRepository->save($pokemon);

        return $pokemon;
    }
}
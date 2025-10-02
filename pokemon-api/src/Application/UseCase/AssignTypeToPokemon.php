<?php 
namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssignTypeToPokemon
{
    private PokemonRepositoryInterface $pokemonRepository;
    private TypeRepositoryInterface $typeRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        PokemonRepositoryInterface $pokemonRepository,
        TypeRepositoryInterface $typeRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->pokemonRepository = $pokemonRepository;
        $this->typeRepository = $typeRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function execute(int $pokemonId, int $typeId)
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
            throw new AccessDeniedHttpException('No tienes permiso para modificar este Pokémon.');
        }

        $type = $this->typeRepository->findById($typeId);
        if (!$type) {
            throw new NotFoundHttpException('Tipo no encontrado.');
        }

        if ($pokemon->getTypes()->contains($type)) {
            throw new \DomainException('El Pokémon ya tiene asignado este tipo.');
        }

        $pokemon->addType($type);
        $this->pokemonRepository->save($pokemon);

        return $pokemon;
    }
}
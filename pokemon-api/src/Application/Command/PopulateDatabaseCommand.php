<?php

namespace App\Application\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Domain\Entity\User;
use App\Domain\Entity\Pokemon;
use App\Domain\Entity\Type;
use App\Domain\Entity\Move;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:populate-database',
    description: 'Pobla la base de datos con Pokémon, movimientos y usuarios desde PokeAPI',
)]
class PopulateDatabaseCommand extends Command
{
    private $em;
    private $client;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->client = $client;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Poblando base de datos...');

        $usersData = [
            ['username' => 'ash', 'roles' => ['ROLE_TRAINER'], 'password' => 'ash123'],
            ['username' => 'misty', 'roles' => ['ROLE_TRAINER'], 'password' => 'misty123'],
            ['username' => 'oak', 'roles' => ['ROLE_PROFESSOR'], 'password' => 'oak123'],
        ];

        foreach ($usersData as $u) {
            $user = new User();
            $user->setUsername($u['username']);
            $user->setRoles($u['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $u['password']));
            $this->em->persist($user);
        }

        $io->success('Usuarios creados');

        $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/type');
        $typeData = $response->toArray()['results'];
        $types = [];

        foreach ($typeData as $t) {
            $type = new Type();
            $type->setName($t['name']);
            $this->em->persist($type);
            $types[$t['name']] = $type;
        }
        $this->em->flush();
        $io->success('Tipos creados');

        $movesResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/move?limit=20');
        $movesList = $movesResponse->toArray()['results'];

        $moves = [];
        foreach ($movesList as $m) {
            $moveData = $this->client->request('GET', $m['url'])->toArray();
            $move = new Move();
            $move->setName($moveData['name']);
            $typeName = $moveData['type']['name'];
            if (isset($types[$typeName])) {
                $move->setType($types[$typeName]);
            } else {
                $move->setType($types['normal']);
            }
            $this->em->persist($move);
            $moves[$moveData['name']] = $move;
        }

        $io->success('Movimientos creados desde PokeAPI');

        $pokemonResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=15');
        $pokemonList = $pokemonResponse->toArray()['results'];

        foreach ($pokemonList as $p) {
            $data = $this->client->request('GET', $p['url'])->toArray();

            $pokemon = new Pokemon();
            $pokemon->setName($data['name']);
            $pokemon->setNickname(null);
            $pokemon->setLevel($data['base_experience'] ?? 5);
            $pokemon->setHealthPoints($data['stats'][0]['base_stat'] ?? 10);
            $pokemon->setAttack($data['stats'][1]['base_stat'] ?? 5);
            $pokemon->setDefense($data['stats'][2]['base_stat'] ?? 5);
            $pokemon->setSpeed($data['stats'][5]['base_stat'] ?? 5);
            $pokemon->setCatchRate(rand(10,100));
            $pokemon->setTrainer(null);

            foreach($data['types'] as $t){
                $typeName = $t['type']['name'];
                if(isset($types[$typeName])){
                    $pokemon->addType($types[$typeName]);
                }
            }

            if(count($moves) >= 2){
                $randomKeys = array_rand($moves, 2);
                foreach ((array)$randomKeys as $k){
                    $pokemon->addMove($moves[$k]);
                }
            }

            $this->em->persist($pokemon);
        }

        $this->em->flush();

        $io->success('Pokémon creados y base de datos poblada');

        return Command::SUCCESS;
    }
}
# Pokémon API

API para gestionar entrenadores, Pokémon y movimientos siguiendo arquitectura hexagonal y buenas prácticas de PHP/Symfony.

## Requisitos

- PHP >= 8.1
- Composer
- Symfony >= 6
- Openssl
- MySQL o MariaDB

## Instalación

1. Clonar el repositorio:

```bash
git clone https://github.com/DiegoTrujillos/prueba-tecnica.git
cd prueba-tecnica
cd pokemon-api
```

2. Instalar dependencias de PHP:

```bash
composer install
```

3. Editar .env y configurar la conexión a la base de datos:

```bash
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/pokemon_api"
```

4. Crear la base de datos:

```bash
php bin/console doctrine:database:create
```

5. Ejecutar las migraciones:

```bash
php bin/console doctrine:migrations:migrate
```

6. Poblar la base de datos:

```bash
 php bin/console app:populate-database
```

7. Genera las claves JWT:

```bash
mkdir -p config/jwt

openssl genrsa -out config/jwt/private.pem -aes256 4096

openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

```
En este paso se le pedirá una passphrase para proteger la clave privada esta debera ser ingresada en .env o igual se puede usar la existente en ese archivo

8. Iniciar el servidor de desarrollo:

```bash
symfony server:start
```

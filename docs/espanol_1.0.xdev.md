# Zyos Install (Symfony Bundle)

**Zyos Install** es una serie de utilidades las cuales pueden ayudar en procesos de desarrollo como despliegues para producción, estas utilidades son ejecutadas a través de la consola de Symfony.

## Instalación

```sh
composer require zyos/zyos_install 1.0.x-dev
```

Si no usa flex (debería hacerlo), debe habilitar el paquete manualmente:

```php
// config/bundles.php
return [
	/** ... **/
	ZyosInstallBundle\ZyosInstallBundle::class => ['all' => true],
];
```

Es necesario crear el archivo de configuración en la ruta:
```sh
config/packages/zyos_install.yaml
```

Puede utilizar la plantilla que se encuentra dentro de nuestro repositorio:

[zyos_install.yaml](https://github.com/NeuralFramework/zyos_install/blob/1.0/src/Resources/template/zyos_install.yaml) 

## Modo de uso
Puede visualizar los diferentes comandos a ejecutar con la consola de Symfony
```sh
php bin/console list
```
Y podra observar los diferentes comandos **zyos** disponibles.

## Configuración
En el proceso de configuración de los diferentes comandos se realizan en el archivo de configuración **config/packages/zyos_install.yaml** los cuales comprenden:

- translation
- environments
- install
- symlink
- mirror
- sql
- cli
- validation
- export

### **Translation**
Este proceso configura el idioma de los mensajes generales y las ayudas de los comandos disponibles para ejecutar
>Solo esta disponible las opciones: es, en


archivo de configuración: **config/packages/zyos_install.yaml**
```yaml
zyos_install:
    translation: 'es'
```

### **Environments**
Los entornos son aquellos en los que se ejecutaran los comandos, no necesariamente tienen que estar configurados los entornos, estos solo aplican como descriptivos dentro de los comandos para diferenciar el proceso a seguir.

>Puede agregar los entornos que usted crea necesario, en caso de que no se configure esta opción o que no este implícito en el archivo de configuración, los entornos por defecto serán **dev** y **prod**

```yaml
zyos_install:
    environments: [  'dev', 'prod', 'prepare', 'clean'  ]
```

### **Install**
Este proceso ejecuta comandos de Symfony de forma ordenada en la configuración, este proceso es una ayuda en la cual ejecuta todos los comandos requeridos sin necesidad de ejecutarlos por el usuario uno por uno. Este proceso principalmente es para ahorrar tiempos y el orden de ejecución.

## Uso
Cuando en la configuración de Symfony se encuentra APP_ENV=dev
```sh
php bin/console zyos:install <entorno>
php bin/console zyos:install dev
```

Cuando en la configuración de Symfony se encuentra APP_ENV=prod
```sh
php bin/console zyos:install <entorno>
php bin/console zyos:install prod --env=dev
```

>Cuando se ejecuta el comando en entorno **prod** se crea un bloqueo para ejecutar el comando correspondiente, si es necesario o requerido ejecutar nuevamente este comando debe eliminarse el archivo de bloqueo ubicado en la ruta: **src/Resources/install/lock.lock**

```yaml
zyos_install:
    install:
        enable: true
        commands:
            - { enable: true, env: 'dev', command: 'doctrine:database:drop', arguments: { --if-exists: true, --force: true } }
            - { enable: true, env: [ 'dev' ], command: 'doctrine:database:create' }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'doctrine:schema:create' }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'doctrine:fixtures:load', arguments: { --append: true, --group: [ '{{env}}' ] } }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'zyos:sql:import', arguments: { environment: '{{env}}' } }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'zyos:create:symlink', arguments: { environment: '{{env}}' } }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'assets:install', arguments: { target: 'public', --symlink: true, --relative: true } }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'cache:clear', arguments: { --no-warmup: true, --no-optional-warmers: true } }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'zyos:execute:validation', arguments: { environment: '{{env}}' } }
```

## Opciones de configuración
- **install: enable:** true para poder utilizar el comando, false para desactivarlo.
- **install: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **install: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando correspondiente.
- **install: commands: - { command: 'comando:symfony' }:** comando Symfony para ser ejecutado
- **install: commands: - { arguments: { opcion: 'valor'} }:** en caso que el comando Symfony requiera pasar argumentos se realiza con el array correspondiente.

>En algunos comandos es necesario pasar un parámetro de entorno, se ha generado el valor: **{{env}}** el cual es el mismo entorno asignado en la ejecución del comando zyos:install <entorno>.

### **Symlink**
Este proceso genera enlaces simbólicos en los cuales sea necesario para su proyecto, ya sea para crear enlaces para el directorio publico y/o otros usos para la aplicación.

## Uso
Cuando en la configuración de Symfony se encuentra APP_ENV=dev
```sh
php bin/console zyos:create:symlink <entorno>
php bin/console zyos:create:symlink dev
```

Cuando en la configuración de Symfony se encuentra APP_ENV=prod
```sh
php bin/console zyos:create:symlink <entorno>
php bin/console zyos:create:symlink prod --env=dev
```

>Cuando se ejecuta el comando en sistemas Windows este genera la creación del directorio respectivo con la copia de la estructura interna de esta.

```yaml
zyos_install:
    symlink:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', origin: '%kernel.project_dir%/directory', destination: 'public/directory' }
            - { enable: true, env: [ 'dev', 'prod' ], origin: '%kernel.project_dir%/src/Resources/public/css', destination: 'public/css' }
```

- **symlink: enable:** true para poder utilizar el comando, false para desactivarlo.
- **symlink: lockable:** este parámetro si su valor es true y existe el archivo de bloqueo ==src/Resources/install/lock.lock== no se ejecutara el comando, en caso que su valor sea false se ejecutara sin limitaciones.
- **symlink: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **symlink: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando
- **symlink: commands: - { origin: 'directorio/origen' }:** directorio y/o archivo de origen
- **symlink: commands: - { destination: 'directorio/destino' }:** directorio y/o archivo de destino (enlace simbólico)

>Es posible configurar un directorio o un archivo como enlace simbólico.

### **Mirror**
Este proceso genera copia de archivos y/o archivos en los cuales sea necesario para su proyecto.

## Uso
Cuando en la configuración de Symfony se encuentra APP_ENV=dev
```sh
php bin/console zyos:create:mirror <entorno>
php bin/console zyos:create:mirror dev
```

Cuando en la configuración de Symfony se encuentra APP_ENV=prod
```sh
php bin/console zyos:create:mirror <entorno>
php bin/console zyos:create:mirror prod --env=dev
```

```yaml
zyos_install:
    mirror:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', origin: '%kernel.project_dir%/directory', destination: 'public/directory' }
            - { enable: true, env: [ 'dev', 'prod' ], origin: '%kernel.project_dir%/src/Resources/public/css', destination: 'public/css' }
```

- **mirror: enable:** true para poder utilizar el comando, false para desactivarlo.
- **mirror: lockable:** este parámetro si su valor es true y existe el archivo de bloqueo ==src/Resources/install/lock.lock== no se ejecutara el comando, en caso que su valor sea false se ejecutara sin limitaciones.
- **mirror: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **mirror: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando
- **mirror: commands: - { origin: 'directorio/origen' }:** directorio y/o archivo de origen
- **mirror: commands: - { destination: 'directorio/destino' }:** directorio y/o archivo de destino

>Es posible configurar un directorio o un archivo como mirror.


### **SQL**
Este proceso genera la carga de archivos SQL directamente a la base de datos.

## Uso
Cuando en la configuración de Symfony se encuentra APP_ENV=dev
```sh
php bin/console zyos:sql:import <entorno>
php bin/console zyos:sql:import dev
```

Cuando en la configuración de Symfony se encuentra APP_ENV=prod
```sh
php bin/console zyos:sql:import <entorno>
php bin/console zyos:sql:import prod --env=dev
```

```yaml
zyos_install:
    sql:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', files: 'archivo.sql' }
            - { enable: true, env: [ 'dev', 'prod' ], files: [ 'archivo1.sql', 'archivo2.sql' ] }
```

- **sql: enable:** true para poder utilizar el comando, false para desactivarlo.
- **sql: lockable:** este parámetro si su valor es true y existe el archivo de bloqueo ==src/Resources/install/lock.lock== no se ejecutara el comando, en caso que su valor sea false se ejecutara sin limitaciones.
- **sql: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **sql: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando
- **sql: commands: - { files: [ 'archivo1.sql', 'archivo2.sql' ] }:** archivos SQL a cargar.
- **sql: commands: - { connection: 'nombre de la conexión DOCTRINE' }:** En caso de manejar multiples conexiones a bases de datos con DOCTRINE, puede indicar el nombre de la conexión para cargar los archivos SQL.

>Los archivos SQL deben estar almacenados en la ruta: **src/Resources/install/sql/** para que el comando pueda cargarlos a través de DOCTRINE.

### **CLI**
Este comando genera la ejecución de un comando personalizado directamente en el sistema operativo del equipo local o servidor.

## Uso
```sh
php bin/console zyos:execute:cli <entorno>
php bin/console zyos:execute:cli dev
```

```yaml
zyos_install:
    cli:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', command: 'ls -al' }
            - { enable: true, env: [ 'dev', 'prod' ], command: 'ls -al' }
```
- **cli: enable:** true para poder utilizar el comando, false para desactivarlo.
- **cli: lockable:** este parámetro si su valor es true y existe el archivo de bloqueo ==src/Resources/install/lock.lock== no se ejecutara el comando, en caso que su valor sea false se ejecutara sin limitaciones.
- **cli: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **cli: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando
- **cli: commands: - { command: 'comando a ejecutar' }:** comando a ejecutar en el sistema operativo.

>La ejecución de los comandos se realiza a través del componente **Symfony Process**


### **Validation**
Este comando es destinado para el proceso de validación de la posible estructura interna del proyecto en la aplicación, se genera la configuración de un archivo o directorio y se configura las validaciones internas que debe realizar.

## Uso
```sh
php bin/console zyos:execute:validation <entorno>
php bin/console zyos:execute:validation dev
```

```yaml
zyos_install:
    validation:
        enable: true
        lockable: true
        commands:
            -   enable: true
                env: 'dev'
                filepath: '%kernel.project_dir%/public/directory'
                validations:
                    - { validation: 'exists' }
                    - { validation: 'is_dir' }
                    - { validation: 'is_not_link' }
                    - { validation: 'validacion_personalizada', params: { llave: 'valor' } }
            -   enable: true
                env: ['dev', 'prod']
                filepath: '%kernel.project_dir%/public/directory/image.png'
                validations:
                    - { validation: 'exists' }
                    - { validation: 'is_file' }
                    - { validation: 'is_readable' }
                    - { validation: 'is_readable' }
                    - { validation: 'validacion_personalizada', params: { llave: 'valor' } }
```

- **validation: enable:** true para poder utilizar el comando, false para desactivarlo.
- **validation: lockable:** este parámetro si su valor es true y existe el archivo de bloqueo ==src/Resources/install/lock.lock== no se ejecutara el comando, en caso que su valor sea false se ejecutara sin limitaciones.
- **validation: commands: - { enable: true }:** true para poder utilizar el comando, false para desactivarlo.
- **validation: commands: - { env: [ 'dev', 'prod' ] }:** entornos los cuales se debe ejecutar el comando
- **validation: commands: - { filepath: 'Ruta del archivo o directorio' }:** directorio y/o archivo el cual se realizaran las validaciones.
- **validation: commands: - { validations: [ 'validación' ] }:** validación a ser aplicada

### **Validaciones Disponibles**
- **exists**: valida si un archivo o directorio existe.
- **not_exists**: valida si un archivo o directorio no existe.
- **is_file**: valida si el filepath es un archivo.
- **is_not_file**: valida si el filepath no es un archivo.
- **is_dir**: valida si el filepath es un directorio.
- **is_not_dir**: valida si el filepath no es un directorio.
- **is_link**: valida si el filepath es un enlace simbólico.
- **is_not_link**: valida si el filepath no es un enlace simbólico.
- **is_executable**: valida si es ejecutable.
- **is_not_executable**: valida no es ejecutable.
- **is_readable**: valida si es posible leer el directorio o archivo.
- **is_not_readable**: valida si no es posible leer el directorio o archivo.
- **is_writable**: valida si es posible escribir en el directorio o archivo.
- **is_not_writable**: valida si no es posible escribir en el directorio o archivo.

### **Validaciones Personalizadas**
Es posible generar validaciones personalizadas, la cuales ayudaran para los procesos que requieren validar más alla de las validaciones disponibles, solo es necesario extender a la interface **ZyosInstallBundle\Interfaces\ValidatorInterface**

```php
<?php
    namespace App\Services;

    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class TestValidator
     *
     * @package App\Services
     */
    class TestValidator implements ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value filepath
         * @param array $params parametros que se requieran en la validación
         *
         * @return bool
         */
        public function validate($value, array $params = []): bool {

            /** Proceso y logica de validación **/
            // Debe retornar un valor booleano
            // True: ok - pass
            // False: failed
            return true;
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            /** Nombre de la validación **/
            return 'Validación Personalizada';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            /** función a utilizar en la configuración **/
            return 'validacion_personalizada';
        }
    }
```
Si se maneja el autoload de clases y autowiring de Symfony se marcara la validación personalizada con el tag ***zyos_install.validators***, si no maneja el autoload de clases y autowiring se debe registrar en el archivo: ***config/services.yaml***

```yaml
services:
    App\Services\TestValidator:
        tags: [ 'zyos_install.validators' ]

```

### **Export**
Este es un proceso **UNICAMENTE PARA MySQL** el cual genera un dump de la base de datos utilizando el cliente ==**mysqldump**==

## Uso
```sh
php bin/console zyos:sql:export <nombre de la configuracion>
php bin/console zyos:sql:export mi_base_datos

# Exportar todos los datos (Estructuras, datos, etc)
php bin/console zyos:sql:export nombre_configuracion
  
# Crear INSERT INTO extendidos
php bin/console zyos:sql:export nombre_configuracion --extended-insert
  
# No crear tablas
php bin/console zyos:sql:export nombre_configuracion --no-create-tables
  
# Exportar solo los INSERT INTO
php bin/console zyos:sql:export nombre_configuracion --no-create-database --no-create-drop-tables --no-create-lock-tables -no-create-tables
  
# Asignar nombre del archivo
php bin/console zyos:sql:export nombre_configuracion --result-file="archivo.sql"

```

```yaml
zyos_install:
    export:
        enable: true
        commands:
            nombre_de_la_configuracion:
                enable: true
                lockable: false
                params:
                    client: mysqldump
                    host: localhost
                    port: 3306
                    username: usuario
                    password: contraseña
                    database: mi_base_datos
```
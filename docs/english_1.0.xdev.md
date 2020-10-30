# Zyos Install (Symfony Bundle)

**Zyos Install** It is a series of utilities which can help in development processes such as production deployments, these utilities are executed through the component Symfony console.

## Installation

```sh
composer require zyos/zyos_install 1.0.x-dev
```

If you don't use flex (you should), you need to enable the package manually:

```php
// config/bundles.php
return [
	/** ... **/
	ZyosInstallBundle\ZyosInstallBundle::class => ['all' => true],
];
```

It is necessary to create the configuration file in the path:
```sh
config/packages/zyos_install.yaml
```

You can use the template found inside our repository:

[zyos_install.yaml](https://github.com/NeuralFramework/zyos_install/blob/1.0/src/Resources/template/zyos_install.yaml) 

## How to use
You can view the different commands to execute with the symfony console
```sh
php bin/console list
```
And you can see the different **zyos** commands available.

## Setting
In the configuration process, the different commands are carried out in the configuration file **config/packages/zyos_install.yaml** which include:

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
This process configures the language of the general messages and the help of the commands available to execute
>Only the options are available: es, en


configuration file: **config/packages/zyos_install.yaml**
```yaml
zyos_install:
    translation: 'en'
```

### **Environments**
The environments are those in which the commands will be executed, the environments do not necessarily have to be configured, they only apply as descriptive within the commands to differentiate the process to follow.

>You can add the environments that you deem necessary, in case this option is not configured or is not implicit in the configuration file, the default environments will be **dev** and **prod**

```yaml
zyos_install:
    environments: [  'dev', 'prod', 'prepare', 'clean'  ]
```

### **Install**
This process executes Symfony commands in an orderly way in the configuration, this process is an aid in which it executes all the required commands without having to execute them one by one by the user. This process is mainly to save time and order of execution.

## How to use
```sh
php bin/console zyos:install <entorno>
php bin/console zyos:install dev
```

>When the command is executed in **prod** environment, a lock is created to execute the corresponding command, if it is necessary or required to execute this command again, the lock file located in the path: **src/Resources/install/lock.lock**

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

## Configuration Options
- **install: enable:** true to be able to use the command, false to disable it.
- **install: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **install: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the corresponding command must be executed.
- **install: commands: - { command: 'comando:symfony' }:** Symfony command to be executed
- **install: commands: - { arguments: { opcion: 'valor'} }:** in case the symfony command requires passing arguments, it is done with the corresponding array.

>In some commands it is necessary to pass an environment parameter, the value has been generated: **{{env}}** which is the same environment assigned in the execution of the zyos: install <environment> command.

### **Symlink**
This process generates symbolic links in which it is necessary for your project, either to create links for the public directory and/or other uses for the application.

## How to use
```sh
php bin/console zyos:create:symlink <entorno>
php bin/console zyos:create:symlink dev
```
>When the command is executed on Windows systems, it generates the creation of the respective directory with the copy of its internal structure.

```yaml
zyos_install:
    symlink:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', origin: '%kernel.project_dir%/directory', destination: 'public/directory' }
            - { enable: true, env: [ 'dev', 'prod' ], origin: '%kernel.project_dir%/src/Resources/public/css', destination: 'public/css' }
```

- **symlink: enable:** true to be able to use the command, false to disable it.
- **symlink: lockable:** If this parameter is true and the lock file exists ==src/Resources/install/lock.lock== the command will not be executed, if its value is false it will be executed without limitations.
- **symlink: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **symlink: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the command must be executed.
- **symlink: commands: - { origin: 'source/directory' }:** source directory or file.
- **symlink: commands: - { destination: 'destination/directory' }:** destination directory or file (symbolic link).

>You can set a directory or file as a symbolic link.

### **Mirror**
This process generates copy of files and/or files in which it is necessary for your project.

## How to use
```sh
php bin/console zyos:create:mirror <entorno>
php bin/console zyos:create:mirror dev
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

- **mirror: enable:** true to be able to use the command, false to disable it.
- **mirror: lockable:** If this parameter is true and the lock file exists ==src/Resources/install/lock.lock== the command will not be executed, if its value is false it will be executed without limitations.
- **mirror: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **mirror: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the command must be executed.
- **mirror: commands: - { origin: 'source/dorectory' }:** source directory or file.
- **mirror: commands: - { destination: 'destination/directory' }:** destination directory or file (symbolic link).

>You can configure a directory or file as a mirror.


### **SQL**
This process generates the upload of SQL files directly to the database.

## How to use
```sh
php bin/console zyos:sql:import <entorno>
php bin/console zyos:sql:import dev
```

```yaml
zyos_install:
    sql:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', files: 'file.sql' }
            - { enable: true, env: [ 'dev', 'prod' ], files: [ 'file1.sql', 'file2.sql' ] }
```

- **sql: enable:** true to be able to use the command, false to disable it.
- **sql: lockable:** If this parameter is true and the lock file exists ==src/Resources/install/lock.lock== the command will not be executed, if its value is false it will be executed without limitations.
- **sql: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **sql: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the command must be executed.
- **sql: commands: - { files: [ 'archivo1.sql', 'archivo2.sql' ] }:** SQL files to load.
- **sql: commands: - { connection: 'DOCTRINE connection name' }:** In case of handling multiple connections to databases with DOCTRINE, you can indicate the name of the connection to load the SQL files.

>The SQL files must be stored in the path: **src/Resources/install/sql/** so that the command can load them through DOCTRINE.

### **CLI**
This command causes the execution of a custom command directly on the operating system of the local computer or server.

## How to use
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
- **cli: enable:** true to be able to use the command, false to disable it.
- **cli: lockable:** If this parameter is true and the lock file exists ==src/Resources/install/lock.lock== the command will not be executed, if its value is false it will be executed without limitations.
- **cli: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **cli: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the command must be executed.
- **cli: commands: - { command: 'command to execute' }:** command to execute in the operating system.

>The execution of the commands is carried out through the **Symfony Component Process**.


### **Validation**
This command is intended for the validation process of the possible internal structure of the project in the application, the configuration of a file or directory is generated and the internal validations to be carried out are configured.

## How to use
```sh
php bin/console zyos:execute:cli <entorno>
php bin/console zyos:execute:cli dev
```

```yaml
zyos_install:
    validation:
        enable: true
        lockable: true
        commands:
            - { enable: true, env: 'dev', filepath: '%kernel.project_dir%/public/directory', validations: 'exists' }
            - { enable: true, env: ['dev', 'prod'], filepath: '%kernel.project_dir%/public/directory', validations: ['exists', 'is_dir'] }
```

- **validation: enable:** true to be able to use the command, false to disable it.
- **validation: lockable:** If this parameter is true and the lock file exists ==src/Resources/install/lock.lock== the command will not be executed, if its value is false it will be executed without limitations.
- **validation: commands: - { enable: true }:** true to be able to use the command, false to disable it.
- **validation: commands: - { env: [ 'dev', 'prod' ] }:** environments in which the command must be executed.
- **validation: commands: - { filepath: 'Path of file or directory' }:** directory and / or file in which the validations are carried out.
- **validation: commands: - { validations: [ 'validator' ] }:** validation to be applied

### **Validaciones Disponibles**
- **exists**: validates if a file or directory exists.
- **is_file**: validates if the filepath is a file.
- **is_dir**:validates if the filepath is a directory.
- **is_link**: validates if the filepath is a symbolic link.
- **is_executable**: validates if it is executable.
- **is_readable**: validates if the directory or file can be read.
- **is_writable**: validates if it is possible to write to the directory or file.


### **Export**
This is a process **ONLY FOR MySQL** which generates a dump of the database using the client ==**mysqldump**==

## How to use
```sh
php bin/console zyos:sql:export <configuration name>
php bin/console zyos:sql:export mi_base_datos

# Export all data (Structures, data, etc)
php bin/console zyos:sql:export configuration_name
  
# Create extended INSERT INTOs
php bin/console zyos:sql:export configuration_name --extended-insert
  
# Don't create tables
php bin/console zyos:sql:export configuration_name --no-create-tables
  
# Export only INSERT INTOs
php bin/console zyos:sql:export configuration_name --no-create-database --no-create-drop-tables --no-create-lock-tables -no-create-tables
  
# Assign file name
php bin/console zyos:sql:export configuration_name --result-file="archivo.sql"

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
                    username: root
                    password: pswd
                    database: my_database
```
<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\Services\Arguments;
    use ZyosInstallBundle\Services\Commands;
    use ZyosInstallBundle\Services\Helpers;
    use ZyosInstallBundle\Services\Skeleton;
    use ZyosInstallBundle\Traits\ExecuteCommand;

    /**
     * Class ZyosInstallCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosInstallCommand extends Command {

        /**
         * Traits
         */
        use ExecuteCommand;
        private $arguments;
        private $container;
        private $commands;
        private $helpers;
        private $filesystem;
        private $skeleton;

        /**
         * ZyosInstallCommand constructor.
         *
         * @param string|null $name
         * @param Arguments $arguments
         * @param ContainerInterface $container
         * @param Commands $commands
         * @param Helpers $helpers
         * @param Filesystem $filesystem
         * @param Skeleton $skeleton
         */
        function __construct(?string $name = null, Arguments $arguments, ContainerInterface $container, Commands $commands, Helpers $helpers, Filesystem $filesystem, Skeleton $skeleton) {

            parent::__construct($name);
            $this->arguments = $arguments;
            $this->container = $container;
            $this->commands = $commands;
            $this->helpers = $helpers;
            $this->filesystem = $filesystem;
            $this->skeleton = $skeleton->validate();
            $this->setHidden($this->skeleton->lockFileExists());
        }

        /**
         * Configure
         */
        protected function configure() {

            $this->setName('zyos:install');
            $this->setDescription('Procesos internos de instalación de la aplicación');
            $this->addArgument('environment', InputArgument::OPTIONAL, 'Entorno de Desarrollo a Ejecutar', 'dev');
        }

        /**
         * Execute
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        protected function execute(InputInterface $input, OutputInterface $output) {

            if ($this->skeleton->lockFileExists()):
                throw new \RuntimeException('No es posible ejecutar este comando, la aplicación se encuentra en producción');
            endif;

            if (!$this->arguments->validateEnvironment($input)):
                throw new \RuntimeException('El entorno NO ES VALIDO');
            endif;

            $io = new SymfonyStyle($input, $output);
            $this->helpers->setSymfonyStyle($io);

            return $this->validate($input, $output);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        private function validate(InputInterface $input, OutputInterface $output) {

            $this->helpers->gettio()->title('Instalación de la Aplicación');
            $this->helpers->gettio()->text([
                'Generá el proceso de ejecución de los diferentes comandos de Doctrine y',
                'comandos personalizados para generar el proceso de instalación de datos ya sea',
                'en entornos de desarrollo y producción este proceso esta pensado para facilitar',
                'el deploy de datos tanto en procesos de manejo de desarrollo multi-linear y',
                'simplicidad para su implementación.'
            ]);
            $this->helpers->gettio()->newLine(2);

            $this->execution($output, 'zyos_install.database_drop', 'doctrine:database:drop');
            $this->execution($output, 'zyos_install.database_create', 'doctrine:database:create');
            $this->execution($output, 'zyos_install.database_schema_create', 'doctrine:schema:create');
            $this->execution($output, 'zyos_install.database_update_schema', 'doctrine:schema:update');
            $this->execution($output, 'zyos_install.database_fixtures', 'doctrine:fixtures:load', ['--group' => [$this->arguments->getEnvironment($input)] ]);
            $this->execution($output, 'zyos_install.database_import_sql', 'zyos:sql:import', ['environment' => $this->arguments->getEnvironment($input)]);
            $this->execution($output, 'zyos_install.assets_install', 'assets:install');
            $this->execution($output, 'zyos_install.create_mirror', 'zyos:create:mirror', ['environment' => $this->arguments->getEnvironment($input)]);
            $this->execution($output, 'zyos_install.create_symlink', 'zyos:create:symlink', ['environment' => $this->arguments->getEnvironment($input)]);
            $this->execution($output, 'zyos_install.cache_clear', 'cache:clear');

            if ($this->arguments->getEnvironment($input) == 'prod'):
                $this->filesystem->dumpFile($this->skeleton->getLockFile(), '');
            endif;

            $this->helpers->gettio()->newLine(1);
            $this->helpers->gettio()->success('Se ha finalizado el proceso de instalación');
            $this->helpers->gettio()->newLine(1);
            return 0;
        }

        /**
         * Execute command
         *
         * @param OutputInterface $output
         * @param string $parameter
         * @param string $command
         * @param array $arguments
         */
        private function execution(OutputInterface $output, string $parameter, string $command, array $arguments = []): void {

            if ($this->container->hasParameter($parameter) AND $this->container->getParameter($parameter)):
                $this->getExecute($output, $command, $arguments);
            endif;
        }
    }
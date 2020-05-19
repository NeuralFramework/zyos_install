<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\MethodBag;
    use ZyosInstallBundle\Services\Arguments;
    use ZyosInstallBundle\Services\Commands;
    use ZyosInstallBundle\Services\Helpers;
    use ZyosInstallBundle\Services\Skeleton;
    use ZyosInstallBundle\Traits\ExecuteCommand;

    /**
     * Class ZyosImportSQLCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosImportSQLCommand extends Command {

        /**
         * Traits
         */
        use ExecuteCommand;

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * @var Commands
         */
        private $commands;

        /**
         * @var Helpers
         */
        private $helpers;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var Arguments
         */
        private $arguments;

        /**
         * ZyosImportSQLCommand constructor.
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

            $this->setName('zyos:sql:import');
            $this->setDescription('Proceso de importar archivos SQL configurados para el proceso de la aplicación');
            $this->addArgument('environment', InputArgument::OPTIONAL, 'Entorno de Desarrollo a Ejecutar', 'dev');
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        protected function execute(InputInterface $input, OutputInterface $output): int {

            if ($this->skeleton->lockFileExists()):
                throw new \RuntimeException('No es posible ejecutar este comando, la aplicación se encuentra en producción');
            endif;

            if (!$this->container->getParameter('zyos_install.database_import_sql')):
                throw new \RuntimeException('No es posible ejecutar este comando');
            endif;

            $io = new SymfonyStyle($input, $output);
            $this->helpers->setSymfonyStyle($io);

            return $this->executeValidation($input, $output);

        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        private function executeValidation(InputInterface $input, OutputInterface $output): int {

            if ($this->container->getParameter('zyos_install.database_import_sql')):
                return $this->executeHolder($input, $output);
            else:
                $this->helpers->gettio()->error('No esta ACTIVADO el proceso para su ejecución');
                return 1;
            endif;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        private function executeHolder(InputInterface $input, OutputInterface $output): int {


            $this->helpers->gettio()->title('Importar Archivos SQL');
            $this->helpers->gettio()->text([
                'Este proceso generá la ejecución del comando de Doctrine para importar los',
                'archivos SQL indicados a la base de datos este proceso solo se ejecutará con',
                'los archivos previamente configurados en el archivo de configuración este',
                'comando es para procesos de desarrollo y producción donde se requiera generar',
                'el pack completo de instalación de la aplicación generada con symfony, proceso',
                'condensado para la implementación.'
            ]);
            $this->helpers->gettio()->newLine(2);

            if (!$this->arguments->validateEnvironment($input)):
                throw new \RuntimeException('El entorno NO ES VALIDO');
            endif;

            $params = new MethodBag($this->container->getParameter('zyos_install.import_sql'));

            if ($params->count() > 0):
                return $this->validateFiles($output, $params, $this->arguments->getEnvironment($input));
            else:
                $this->helpers->gettio()->success('No hay archivos configurados para el procesamiento');
                return 1;
            endif;
        }

        /**
         * @param OutputInterface $output
         * @param MethodBag $params
         * @param string $environment
         *
         * @return int
         */
        private function validateFiles(OutputInterface $output, MethodBag $params, string $environment): int {

            foreach ($params->all() AS $array):
                if (!$this->filesystem->exists($array['file'])):
                    throw new \RuntimeException(sprintf('El archivo: %s NO EXISTE', $array['file']));
                endif;
            endforeach;

            return $this->executeProcess($output, $params, $environment);
        }

        /**
         * @param OutputInterface $output
         * @param MethodBag $params
         * @param string $environment
         *
         * @return int
         */
        private function executeProcess(OutputInterface $output, MethodBag $params, string $environment): int {

            foreach ($params->all() AS $array):
                if (in_array($environment, $array['environment'])):
                    $this->getExecute($output, 'doctrine:database:import', ['file' => $array['file']]);
                endif;
            endforeach;

            $this->helpers->gettio()->newLine(1);
            $this->helpers->gettio()->success('Se han generado los procesos de importar de los archivos correctamente');
            $this->helpers->gettio()->newLine(1);
            return 0;
        }
    }
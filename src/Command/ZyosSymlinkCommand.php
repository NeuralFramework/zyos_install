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

    /**
     * Class ZyosSymlinkCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosSymlinkCommand extends Command {

        /**
         * @var Arguments
         */
        private $arguments;

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * @var Commands
         */
        private $commands;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var Helpers
         */
        private $helpers;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * ZyosSymlinkCommand constructor.
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

            $this->setName('zyos:create:symlink');
            $this->setDescription('Genera la creación de Links Simbólicos configurados');
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

            if (!$this->container->getParameter('zyos_install.create_symlink')):
                throw new \RuntimeException('No es posible ejecutar este comando');
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
        private function validate(InputInterface $input, OutputInterface $output): int {

            $this->helpers->gettio()->title('Creación de Symlinks');
            $this->helpers->gettio()->text([
                'Generá el proceso de creación de los enlaces simbólicos del punto de origen',
                'al punto de destino el cuál se aplican para el proceso de desarrollo y/o deploy',
                'de producción.'
            ]);

            $params = new MethodBag($this->container->getParameter('zyos_install.symlink'));

            if ($params->count() > 0):
                return $this->execution($params, $output, $this->arguments->getEnvironment($input));
            else:
                $this->helpers->gettio()->success('No hay archivos configurados para el procesamiento');
                return 1;
            endif;
        }

        /**
         * @param MethodBag $parameters
         * @param OutputInterface $output
         * @param string $environment
         *
         * @return int
         */
        private function execution(MethodBag $parameters, OutputInterface $output, string $environment): int {

            $list = [];

            foreach ($parameters->all() AS $array):

                if (in_array($environment, $array['environment'])):
                    if ($this->filesystem->exists($array['origin'])):
                        $this->validateProcess($array['origin'], $array['destination']);
                        $list[] = 1;
                    else:
                        throw new \RuntimeException(sprintf('El directorio o archivo de origen: %s NO EXISTE', $array['origin']));
                    endif;
                endif;
            endforeach;

            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('<comment>Cantidad de Registros Procesados:</comment> <info>%s registros</info>', count($list)));
            $this->helpers->gettio()->success('Se ha ejecutado la creación de Symlink correctamente');
            return 0;
        }

        /**
         * @param string $origin
         * @param string $destination
         */
        private function validateProcess(string $origin, string $destination): void {

            if ($this->filesystem->exists($destination)):
                $this->filesystem->remove($destination);
            endif;

            $this->filesystem->symlink($origin, $destination, true);
        }
    }
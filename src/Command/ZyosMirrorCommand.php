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
     * Class ZyosMirrorCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosMirrorCommand extends Command {

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * @var Helpers
         */
        private $helpers;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * @var Commands
         */
        private $commands;

        /**
         * @var Arguments
         */
        private $arguments;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * ZyosMirrorCommand constructor.
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

            $this->setName('zyos:create:mirror');
            $this->setDescription('Genera la copia de directorios configurados');
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

            if (!$this->container->getParameter('zyos_install.create_mirror')):
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

            $this->helpers->gettio()->title('Creación de Mirror de Directorios');
            $this->helpers->gettio()->text([
                'Generá el proceso de copia de directorios del punto de origen al punto de',
                'destino el cuál se aplican para el proceso de desarrollo y/o deploy de',
                'producción.'
            ]);

            $params = new MethodBag($this->container->getParameter('zyos_install.mirror'));

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
                        throw new \RuntimeException(sprintf('El directorio de origen: %s NO EXISTE', $array['origin']));
                    endif;
                endif;
            endforeach;

            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('<comment>Cantidad de Registros Procesados:</comment> <info>%s registros</info>', count($list)));
            $this->helpers->gettio()->success('Se ha ejecutado la creación de Mirror correctamente');
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

            $this->filesystem->mirror($origin, $destination);
        }
    }
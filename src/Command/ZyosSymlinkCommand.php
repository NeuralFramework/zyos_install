<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use ZyosInstallBundle\Component\SymfonySymlink;
    use ZyosInstallBundle\ParameterBag\MethodBag;
    use ZyosInstallBundle\Parameters\Commands;
    use ZyosInstallBundle\Parameters\Parameters;
    use ZyosInstallBundle\Services\Arguments;
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
         * @var Helpers
         */
        private $helpers;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * @var SymfonySymlink
         */
        private $symfonySymlink;

        /**
         * @var array
         */
        private $count = [];

        /**
         * ZyosSymlinkCommand constructor.
         *
         * @param string|null $name
         * @param Arguments $arguments
         * @param Helpers $helpers
         * @param Parameters $parameters
         * @param Skeleton $skeleton
         * @param SymfonySymlink $symfonySymlink
         */
        function __construct(?string $name = null, Arguments $arguments, Helpers $helpers, Parameters $parameters, Skeleton $skeleton, SymfonySymlink $symfonySymlink) {

            parent::__construct($name);
            $this->arguments = $arguments;
            $this->helpers = $helpers;
            $this->parameters = $parameters;
            $this->skeleton = $skeleton->validate();
            $this->symfonySymlink = $symfonySymlink;
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

            $io = new SymfonyStyle($input, $output);
            $this->helpers->setSymfonyStyle($io);

            if ($this->skeleton->lockFileExists()):
                $this->helpers->gettio()->error('No es posible ejecutar el comando');
                return 1;
            endif;

            if (!$this->arguments->validateEnvironment($input)):
                $this->helpers->gettio()->error('El entorno indicado no es válido');
                return 1;
            endif;

            if (!$this->parameters->getSymlinkEnable()):
                $this->helpers->gettio()->error('El comando solicitado esta deshabilitado');
                return 1;
            endif;

            return $this->validate($input, $output);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         */
        private function validate(InputInterface $input, OutputInterface $output): int {

            $this->helpers->gettio()->title('Creación de Links Simbólicos - Symlinks');
            $this->helpers->gettio()->text([
                'Proceso de ejecución de comandos para la implementación del despliegue de la',
                'aplicación en el entorno requerido este proceso solo es una ayuda para',
                'simplificar el desarrollo o el paso a producción.'
            ]);
            $this->helpers->gettio()->newLine(2);

            $params = new MethodBag($this->parameters->getSymlinkConfig());

            if ($params->count() > 0):
                return $this->validateExists($input, $output, $params);
            else:
                $this->helpers->gettio()->success('No hay Symlinks para generar');
                return 1;
            endif;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         */
        private function validateExists(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            $this->helpers->gettio()->section('Validando existencia de directorios o archivos de origen');
            $this->helpers->gettio()->progressStart($params->count());

            foreach ($params->all() AS $item):

                if (!$this->symfonySymlink->exists($item['origin'])):
                    throw new \RuntimeException(sprintf('No existe el directorio o archivo: %s', $item['origin']));
                endif;

            endforeach;

            $this->helpers->gettio()->progressFinish();

            return $this->create($input, $output, $params);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         */
        private function create(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            $this->helpers->gettio()->section('Creando los Links Simbólicos - Symlinks Solicitados');
            $this->helpers->gettio()->progressStart($params->count());

            foreach ($params->all() AS $item):
                $this->validateEnvironment($input, $output, $item);
                $this->helpers->gettio()->progressAdvance();
            endforeach;

            $this->helpers->gettio()->progressFinish();
            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('Se ha ejecutado: <comment>%s comandos</comment>', count($this->count)));
            $this->helpers->gettio()->success('Se ha finalizado el proceso de creación Symlinks');

            return 0;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param array $array
         */
        private function validateEnvironment(InputInterface $input, OutputInterface $output, array $array = []): void {

            if (in_array($this->arguments->getEnvironment($input), $array['environment'])):
                $this->symfonySymlink->createIfNotExists($array['origin'], $array['destination']);
                $this->count[] = 1;
            endif;
        }
    }
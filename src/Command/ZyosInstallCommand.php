<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use ZyosInstallBundle\Component\SymfonyCommand;
    use ZyosInstallBundle\ParameterBag\MethodBag;
    use ZyosInstallBundle\Parameters\Parameters;
    use ZyosInstallBundle\Services\Arguments;
    use ZyosInstallBundle\Services\Helpers;
    use ZyosInstallBundle\Services\Skeleton;

    /**
     * Class ZyosInstallCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosInstallCommand extends Command {

        /**
         * @var Arguments
         */
        private $arguments;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * @var Helpers
         */
        private $helpers;

        /**
         * @var SymfonyCommand
         */
        private $symfonyCommand;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var array
         */
        private $count = [];

        /**
         * ZyosInstallCommand constructor.
         *
         * @param string|null $name
         * @param Arguments $arguments
         * @param Helpers $helpers
         * @param Parameters $parameters
         * @param Skeleton $skeleton
         * @param SymfonyCommand $symfonyCommand
         */
        function __construct(?string $name = null, Arguments $arguments, Helpers $helpers, Parameters $parameters, Skeleton $skeleton, SymfonyCommand $symfonyCommand) {

            parent::__construct($name);
            $this->arguments = $arguments;
            $this->helpers = $helpers;
            $this->parameters = $parameters;
            $this->skeleton = $skeleton->validate();
            $this->symfonyCommand = $symfonyCommand;
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
         * @throws \Exception
         */
        protected function execute(InputInterface $input, OutputInterface $output) {

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

            if (!$this->parameters->getInstallEnable()):
                $this->helpers->gettio()->error('El comando solicitado esta deshabilitado');
                return 1;
            endif;

            return $this->executeProcess($input, $output);
        }

        /**
         * Execute Process
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         * @throws \Exception
         */
        private function executeProcess(InputInterface $input, OutputInterface $output) {

            $this->helpers->gettio()->title('Despliegue - Instalación de la Aplicación');
            $this->helpers->gettio()->text([
                'Proceso de ejecución de comandos para la implementación del despliegue de la',
                'aplicación en el entorno requerido este proceso solo es una ayuda para',
                'simplificar el desarrollo o el paso a producción.'
            ]);
            $this->helpers->gettio()->newLine(2);


            $environment = $this->arguments->getEnvironment($input);
            $params = new MethodBag($this->parameters->getInstallConfig());

            if ($params->count() > 0):
                return $this->existsCommands($input, $output, $params, $environment);
            else:
                $this->helpers->gettio()->success('No hay comandos registrados para ejecutar');
                return 0;
            endif;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         * @param string $environment
         *
         * @return int
         * @throws \Exception
         */
        private function existsCommands(InputInterface $input, OutputInterface $output, MethodBag $params, string $environment): int {

            $this->helpers->gettio()->section('Validando Existencia de Comandos');
            $count = [];

            foreach ($params AS $item):
                if ('symfony_command' === $item['type']):
                    $this->getApplication()->find($item['command']);
                    $count[] = 1;
                endif;
            endforeach;

            $this->helpers->gettio()->text(sprintf('Cantidad de comandos validados: <comment>%s comandos</comment>', count($count)));
            return $this->executeCommands($input, $output, $params, $environment);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         * @param string $environment
         *
         * @return int
         * @throws \Exception
         */
        private function executeCommands(InputInterface $input, OutputInterface $output, MethodBag $params, string $environment): int {

            $this->helpers->gettio()->section('Validando Existencia de Comandos');

            foreach ($params AS $item):
                if ($item['enable']):
                    $this->validateEnvironment($output, $environment, $item);
                endif;
            endforeach;

            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('Se ha ejecutado: <comment>%s comandos</comment>', count($this->count)));
            $this->helpers->gettio()->success('Se ha finalizado el proceso');

            if ('prod' == $this->arguments->getEnvironment($input)):
                $this->skeleton->createLockFile();
            endif;

            return 0;
        }

        /**
         * @param OutputInterface $output
         * @param string $environment
         * @param array $params
         *
         * @return void
         * @throws \Exception
         */
        private function validateEnvironment(OutputInterface $output, string $environment, array $params = []): void {

            if (in_array($environment, $params['environment'])):
                if ('symfony_command' === $params['type']):
                    $this->symfonyCommand->execute($this->getApplication(), $output, $params['command'], $params['arguments'], $environment);
                    $this->count[] = 1;
                endif;
            endif;
        }
    }
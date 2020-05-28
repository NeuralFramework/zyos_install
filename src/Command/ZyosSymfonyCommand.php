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
     * Class ZyosSymfonyCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosSymfonyCommand extends Command {

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
         * @var SymfonyCommand
         */
        private $symfonyCommand;

        /**
         * @var array
         */
        private $count = [];

        /**
         * ZyosSymfonyCommand constructor.
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
        }

        /**
         * Configure
         */
        protected function configure() {

            $this->setName('zyos:execute:commands');
            $this->setDescription('Ejecuta comandos de Symfony personalizados desde la configuración');
            $this->addArgument('environment', InputArgument::OPTIONAL, 'Entorno de Desarrollo a Ejecutar', 'dev');
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return int
         * @throws \Exception
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

            if (!$this->parameters->getCommandsEnable()):
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
         * @throws \Exception
         */
        private function validate(InputInterface $input, OutputInterface $output): int {

            $this->helpers->gettio()->title('Ejecutar Comandos Symfony');
            $this->helpers->gettio()->text([
                'Proceso de ejecución de comandos para la implementación del despliegue de la',
                'aplicación en el entorno requerido este proceso solo es una ayuda para',
                'simplificar el desarrollo o el paso a producción.'
            ]);
            $this->helpers->gettio()->newLine(2);

            $params = new MethodBag($this->parameters->getCommandsConfig());

            if ($params->count() > 0):
                return $this->validateExists($input, $output, $params);
            else:
                $this->helpers->gettio()->success('No hay comandos configurados para ejecutar');
                return 1;
            endif;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         * @throws \Exception
         */
        private function validateExists(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            $this->helpers->gettio()->section('Validando existencia de comandos de Symfony');

            $this->helpers->gettio()->progressStart($params->count());
            foreach ($params AS $param):
                $this->getApplication()->find($param['command']);
                $this->helpers->gettio()->progressAdvance();
            endforeach;
            $this->helpers->gettio()->progressFinish();

            return $this->executeProcess($input, $output, $params);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         * @throws \Exception
         */
        private function executeProcess(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            $this->helpers->gettio()->section('Ejecutando los comandos de Symfony configurados');

            foreach ($params AS $param):
                $this->validateEnvironment($input, $output, $param);
            endforeach;

            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('Se ha ejecutado: <comment>%s comandos</comment>', count($this->count)));
            $this->helpers->gettio()->success('Se ha finalizado el proceso de ejecución de comandos');

            return 0;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param array $params
         *
         * @throws \Exception
         */
        private function validateEnvironment(InputInterface $input, OutputInterface $output, array $params = []): void {

            $environment = $this->arguments->getEnvironment($input);

            if (in_array($environment, $params['environment'])):
                $this->symfonyCommand->execute($this->getApplication(), $output, $params['command'], $params['arguments'], $environment);
                $this->count[] = 1;
            endif;
        }
    }
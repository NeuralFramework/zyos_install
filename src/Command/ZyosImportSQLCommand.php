<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use ZyosInstallBundle\Component\SymfonyImportSQL;
    use ZyosInstallBundle\ParameterBag\MethodBag;
    use ZyosInstallBundle\Parameters\Parameters;
    use ZyosInstallBundle\Services\Arguments;
    use ZyosInstallBundle\Services\Helpers;
    use ZyosInstallBundle\Services\Skeleton;

    /**
     * Class ZyosImportSQLCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosImportSQLCommand extends Command {

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
         * @var SymfonyImportSQL
         */
        private $importSQL;

        /**
         * @var array
         */
        private $count = [];

        /**
         * ZyosImportSQLCommand constructor.
         *
         * @param string|null $name
         * @param Arguments $arguments
         * @param Helpers $helpers
         * @param Parameters $parameters
         * @param Skeleton $skeleton
         * @param SymfonyImportSQL $importSQL
         */
        function __construct(?string $name = null, Arguments $arguments, Helpers $helpers, Parameters $parameters, Skeleton $skeleton, SymfonyImportSQL $importSQL) {

            parent::__construct($name);
            $this->arguments = $arguments;
            $this->helpers = $helpers;
            $this->parameters = $parameters;
            $this->skeleton = $skeleton->validate();
            $this->importSQL = $importSQL;
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

            if (!$this->parameters->getSQLImportEnable()):
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

            $this->helpers->gettio()->title('Importar Archivos SQL - Ejecutar Archivos SQL en la Base de Datos');
            $this->helpers->gettio()->text([
                'Proceso de ejecución de comandos para la implementación del despliegue de la',
                'aplicación en el entorno requerido este proceso solo es una ayuda para',
                'simplificar el desarrollo o el paso a producción.'
            ]);
            $this->helpers->gettio()->newLine(2);

            $params = new MethodBag($this->parameters->getSQLImportConfig());

            if ($params->count() > 0):
                return $this->validateExists($input, $output, $params);
            else:
                $this->helpers->gettio()->success('No hay configurado archivos para importar');
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

            $this->helpers->gettio()->section('Validando existencia de archivos registrados en la configuración');
            $this->helpers->gettio()->progressStart($params->count());

            foreach ($params->all() AS $item):
                $this->importSQL->existsFiles($item['files']);
                $this->helpers->gettio()->progressAdvance();
            endforeach;

            $this->helpers->gettio()->progressFinish();
            return $this->executesCommands($input, $output, $params);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         * @throws \Exception
         */
        private function executesCommands(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            $this->helpers->gettio()->section('Ejecutando los comandos para el proceso de importar archivos');

            foreach ($params->all() AS $item):
                $this->validateEnvironment($input, $output, $item);
            endforeach;

            $this->helpers->gettio()->newLine(2);
            $this->helpers->gettio()->text(sprintf('Se ha ejecutado: <comment>%s comandos</comment>', count($this->count)));
            $this->helpers->gettio()->success('Se ha finalizado el proceso de importación de archivos SQL');

            return 0;
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param array $array
         *
         * @throws \Exception
         */
        private function validateEnvironment(InputInterface $input, OutputInterface $output, array $array = []): void {

            if (in_array($this->arguments->getEnvironment($input), $array['environment'])):
                $this->importSQL->executeCommand($this->getApplication(), $output, $array['files']);
                $this->count[] = 1;
            endif;
        }
    }
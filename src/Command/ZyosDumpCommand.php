<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use ZyosInstallBundle\Component\SymfonyShell;
    use ZyosInstallBundle\Export\MySQLDump;
    use ZyosInstallBundle\ParameterBag\MethodBag;
    use ZyosInstallBundle\Parameters\Parameters;
    use ZyosInstallBundle\Services\Helpers;
    use ZyosInstallBundle\Services\Skeleton;

    /**
     * Class ZyosDumpCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosDumpCommand extends Command {

        /**
         * @var Helpers
         */
        private $helpers;

        /**
         * @var Skeleton
         */
        private $skeleton;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var SymfonyShell
         */
        private $shell;

        /**
         * ZyosDumpCommand constructor.
         *
         * @param string|null $name
         * @param Helpers $helpers
         * @param Parameters $parameters
         * @param Skeleton $skeleton
         * @param SymfonyShell $shell
         */
        function __construct(?string $name = null, Helpers $helpers, Parameters $parameters, Skeleton $skeleton, SymfonyShell $shell) {

            parent::__construct($name);
            $this->helpers = $helpers;
            $this->parameters = $parameters;
            $this->skeleton = $skeleton->validate();
            $this->shell = $shell;
        }

        /**
         * configure
         */
        protected function configure() {

            $this->setName('zyos:sql:export');
            $this->setDescription('Comando que ejecuta el proceso del dump de la base de datos seleccionada');
            $this->addArgument('connection', InputArgument::REQUIRED, 'Configuración de Base de Datos Registrada');
            $this->addOption('all', null, InputOption::VALUE_NONE, 'DUMP ALL DATA <comment>MySQL</comment>');
            $this->addOption('no-create-database', null, InputOption::VALUE_NONE, 'CREATE DATABASE <comment>MySQL</comment>');
            $this->addOption('no-create-info', null, InputOption::VALUE_NONE, 'Don\'t write table creation info <comment>MySQL</comment>');
            $this->addOption('no-data', null, InputOption::VALUE_NONE, 'No row information <comment>MySQL</comment>');
            $this->addOption('extended-insert', null, InputOption::VALUE_NONE, 'Use multiple-row INSERT syntax that include several VALUES lists <comment>MySQL</comment>');
            $this->addOption('lock-tables', null, InputOption::VALUE_NONE, 'Lock all tables for read <comment>MySQL</comment>');
            $this->addOption('drop-tables', null, InputOption::VALUE_NONE, 'Add a DROP TABLE before each create <comment>MySQL</comment>');
            $this->addOption('result-file', null, InputOption::VALUE_REQUIRED, 'output Filename');
        }

        /**
         * Execute
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         * @return int
         */
        protected function execute(InputInterface $input, OutputInterface $output): int {

            $io = new SymfonyStyle($input, $output);
            $this->helpers->setSymfonyStyle($io);

            $connection = $input->getArgument('connection');
            $params = new MethodBag($this->parameters->getDumpConnections());

            if (!$this->parameters->getDumpEnable()):
                $this->helpers->gettio()->error('No es posible ejecutar el comando');
                return 1;
            endif;

            if (!$params->has($connection)):
                $this->helpers->gettio()->error(sprintf('La conexión: [%s] NO EXISTE', $connection));
                return 1;
            endif;

            $this->helpers->gettio()->title('Dump - Export de Base de Datos');
            $this->helpers->gettio()->text([
                'Proceso de ejecución de comandos para la implementación del despliegue de la',
                'aplicación en el entorno requerido este proceso solo es una ayuda para',
                'simplificar el desarrollo o el paso a producción.'
            ]);

            $parameters = $params->self($connection);
            $parameters->set('all', $input->getOption('all'));
            $parameters->set('no_create_database', $input->getOption('no-create-database'));
            $parameters->set('no_create_info', $input->getOption('no-create-info'));
            $parameters->set('no_data', $input->getOption('no-data'));
            $parameters->set('extended_insert', $input->getOption('extended-insert'));
            $parameters->set('lock_tables', $input->getOption('lock-tables'));
            $parameters->set('drop_tables', $input->getOption('drop-tables'));
            $parameters->set('result_file', $input->getOption('result-file'));
            $parameters->set('name', $connection);

            return $this->validate($input, $output, $parameters);
        }

        /**
         * @param InputInterface $input
         * @param OutputInterface $output
         * @param MethodBag $params
         *
         * @return int
         */
        private function validate(InputInterface $input, OutputInterface $output, MethodBag $params): int {

            if ('mysqldump' === $params->get('client')):
                $command = new MySQLDump($params, $this->skeleton->getDump());
                $this->shell->runArray($command->create());
                $this->helpers->gettio()->newLine(2);
                $this->helpers->gettio()->success('Archivo DUMP generado: '.$command->getName());
            endif;

            return 0;
        }
    }
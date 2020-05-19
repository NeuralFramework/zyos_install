<?php

    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Process\Process;
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
         * ZyosDumpCommand constructor.
         *
         * @param string|null $name
         * @param Helpers $helpers
         * @param Skeleton $skeleton
         */
        function __construct(?string $name = null, Helpers $helpers, Skeleton $skeleton) {

            parent::__construct($name);
            $this->helpers = $helpers;
            $this->skeleton = $skeleton->validate();
        }

        /**
         * configure
         */
        protected function configure() {

            $this->setName('zyos:dump');
            $this->setDescription('Comando que ejecuta el proceso del dump de la base de datos seleccionada');
            $this->addArgument('engine', InputArgument::OPTIONAL, 'Motor de Base de Datos', 'mysql');
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
            $this->helpers->gettio()->title('Copia de Seguridad - DUMP de Base de Datos');
            $this->helpers->gettio()->text([
                'Generá la ejecución del comando correspondiente para crear al copia de',
                'seguridad en el directorio pre-configurado en este punto se ejecutará',
                'a través del cliente instalado en el servidor para dicho propósito.'
            ]);
            $this->helpers->gettio()->newLine(2);

            $option = $this->helpers->getChoice($input->getArgument('engine'), [
                1 => 'mysql'
            ]);

            if (1 == $option):
                $this->executeCommand('getMysqlDump', $input);
            endif;

            return 0;
        }

        /**
         * Create structure of dumps and create
         * filename
         *
         * @param InputInterface $input
         *
         * @return string
         */
        private function createFilename(InputInterface $input): string {

            $path = $this->skeleton->getDump();
            $filename = $input->getOption('result-file') ?: sprintf('%s.sql', date('Ymd_his_A'));
            return sprintf('%s/%s', $path, $filename);
        }

        /**
         * Create command array
         *
         * @param InputInterface $input
         *
         * @return array
         */
        private function getMysqlDump(InputInterface $input): array {

            return array_filter([
                'mysqldump',
                sprintf('--user="%s"', $_ENV['DATABASE_USER']),
                sprintf('--password="%s"', $_ENV['DATABASE_PASSWORD']),
                sprintf('--extended-insert=%s', $input->getOption('extended-insert') ? 'true': 'false'),
                $input->getOption('all') ? '' : sprintf('--no-create-db=%s', $input->getOption('no-create-database') ? 'true' : 'false'),
                $input->getOption('all') ? '' : sprintf('--no-create-info=%s', $input->getOption('no-create-info') ? 'true' : 'false'),
                $input->getOption('all') ? '' : sprintf('--no-data=%s', $input->getOption('no-data') ? 'true' : 'false'),
                sprintf('--result-file="%s"', $this->createFilename($input)),
                sprintf('--add-drop-table=%s', $input->getOption('drop-tables') ? 'true': 'false'),
                sprintf('--lock-tables=%s', $input->getOption('lock-tables') ? 'true': 'false'),
                '--databases',
                $_ENV['DATABASE_DBNAME']
            ]);
        }

        /**
         * Execute command
         *
         * @param string $method
         * @param InputInterface $input
         */
        private function executeCommand(string $method, InputInterface $input): void {

            $command = implode(' ', call_user_func_array([$this, $method], [$input]));

            $process = Process::fromShellCommandline($command);
            $process->run();
            while ($process->isRunning()):endwhile;

            $this->helpers->gettio()->success(sprintf('%s [%s]', $process->getOutput(), $input->getOption('result-file')));
        }
    }
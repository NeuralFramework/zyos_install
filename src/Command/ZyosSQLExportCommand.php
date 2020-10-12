<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 2/10/20
     * Time: 11:41 PM
     */
    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\Process\Process;
    use ZyosInstallBundle\Export\Manager;
    use ZyosInstallBundle\Service\Parameters;

    /**
     * Class ZyosSQLExportCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosSQLExportCommand extends Command {

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * ZyosSQLExportCommand constructor.
         *
         * @param string|null $name
         * @param Parameters  $parameters
         */
        function __construct(?string $name = null, Parameters $parameters) {

            parent::__construct($name);
            $this->parameters = $parameters;
            $this->setHidden($this->parameters->hiddenExport());
            $this->setHelp($this->parameters->translateHelp('zyos.sql.export.help'));
        }

        /**
         * Configure
         *
         * @return void
         */
        protected function configure(): void {

            $this->setName('zyos:sql:export');
            $this->setDescription('Genera el proceso de exportar la base de datos de la aplicación');
            $this->addArgument('configuration', InputArgument::REQUIRED, 'Configuración a ejecutar');
            $this->addOption('extended-insert', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> Utiliza la sintaxis INSERT en una fila que incluya varias listas de VALORES');
            $this->addOption('no-create-database', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> No crear sentencia <info>CREATE DATABASE</info>');
            $this->addOption('no-create-drop-tables', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> No crear sentencia <info>DROP TABLE</info>');
            $this->addOption('no-create-insert', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> No crear senctencias <info>INSERT INTO</info>');
            $this->addOption('no-create-lock-tables', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> No crear senctencias <info>LOCK TABLES</info>');
            $this->addOption('no-create-tables', null, InputOption::VALUE_NONE, '<comment>[MySQL]</comment> No crear senctencias <info>CREATE TABLE</info>');
            $this->addOption('result-file', null, InputOption::VALUE_REQUIRED, '<comment>[MySQL]</comment> Nombre del archivo de salida');
        }

        /**
         * Execute command
         *
         * @param InputInterface  $input
         * @param OutputInterface $output
         *
         * @return int
         */
        protected function execute(InputInterface $input, OutputInterface $output): int {

            $configuration = $input->getArgument('configuration');
            $io = new SymfonyStyle($input, $output);

            if ($this->parameters->structure()):
                $this->validateEnable($io, $input, $output, $configuration);
            else:
                $io->error($this->parameters->translate('No es posible crear la estructura en la ruta: %path%', ['%path%' => $this->parameters->getPath()]));
            endif;

            return 0;
        }

        /**
         * Validate enable command
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         *
         * @return void
         */
        private function validateEnable(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration): void {

            if ($this->parameters->enableExport()):
                $this->validateCount($io, $input, $output, $configuration);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Desactivado'));
            endif;
        }

        /**
         * Validate count commands
         *
         * @param SymfonyStyle $io
         * @param string       $environment
         *
         * @return void
         */
        private function validateCount(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration): void {

            $params = $this->parameters->getExport();

            if ($params->count() > 0):
                $this->validateExistsConfiguration($io, $input, $output, $configuration, $params);
            else:
                $io->error($this->parameters->translate('No Hay Comandos para Ejecutar, Cantidad: %count%', ['%count%' => $params->count()]));
            endif;
        }

        /**
         * Validate exists configuration
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateExistsConfiguration(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            if ($parameterBag->has($configuration)):
                $this->validateEnableCommands($io, $input, $output, $configuration, new ParameterBag($parameterBag->get($configuration)));
            else:
                $io->error($this->parameters->translate('La configuración: %config% No Existe', ['%config%' => $configuration]));
            endif;
        }

        /**
         * Get enable commands
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateEnableCommands(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            if ($parameterBag->getBoolean('enable')):
                $this->validateIsLockCommand($io, $input, $output, $configuration, $parameterBag);
            else:
                $io->error($this->parameters->translate('El comando %command% No se encuentra activo', ['%command%' => $configuration]));
            endif;
        }

        /**
         * Validate lock command
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateIsLockCommand(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            if ($parameterBag->getBoolean('lockable')):
                $this->validateExistsLockFile($io, $input, $output, $configuration, new ParameterBag($parameterBag->get('params', [])));
            else:
                $this->validateParamsCount($io, $input, $output, $configuration, new ParameterBag($parameterBag->get('params', [])));
            endif;
        }

        /**
         * Validate exists lock file
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateExistsLockFile(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            if (!$this->parameters->existsLockFile()):
                $this->validateParamsCount($io, $input, $output, $configuration, $parameterBag);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Bloqueado'));
            endif;
        }

        /**
         * Validate params count
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateParamsCount(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            if ($parameterBag->count() > 0):
                $this->executeShellCommand($io, $input, $output, $configuration, $parameterBag);
            else:
                $io->error($this->parameters->translate('No hay parametros para ejecutar el comando %command%', ['%command%' => $configuration]));
            endif;
        }

        /**
         * Execute shell command
         *
         * @param SymfonyStyle    $io
         * @param InputInterface  $input
         * @param OutputInterface $output
         * @param string          $configuration
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function executeShellCommand(SymfonyStyle $io, InputInterface $input, OutputInterface $output, string $configuration, ParameterBag $parameterBag): void {

            $parameterBag->set('result-file', $this->getResultFile($input));
            $parameterBag->set('extended-insert', $input->getOption('extended-insert'));
            $parameterBag->set('no-create-database', $input->getOption('no-create-database'));
            $parameterBag->set('no-create-insert', $input->getOption('no-create-insert'));
            $parameterBag->set('no-create-tables', $input->getOption('no-create-tables'));
            $parameterBag->set('no-create-lock-tables', !$input->getOption('no-create-lock-tables'));
            $parameterBag->set('configuration_name', $configuration);
            $manager = new Manager($parameterBag, $this->parameters->getPath());

            $this->executeCommandShell($output, $manager);
            $io->success($this->parameters->translate('Se ha finalizado el proceso de ejecución de comandos'));
        }

        /**
         * Get file name
         *
         * @param InputInterface $input
         *
         * @return string|null
         */
        private function getResultFile(InputInterface $input): ?string {

            $name = $input->getOption('result-file');
            return is_string($name) ? $name : null;
        }

        /**
         * Execute command
         *
         * @param OutputInterface $output
         * @param Manager         $manager
         *
         * @return void
         */
        private function executeCommandShell(OutputInterface $output, Manager $manager): void {

            $process = Process::fromShellCommandline($manager->getCommand());
            $process->run();
            while ($process->isRunning()):endwhile;

            $output->writeln($process->getOutput());
        }
    }
<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 30/09/20
     * Time: 01:20 AM
     */
    namespace ZyosInstallBundle\Command;

    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use ZyosInstallBundle\Service\Parameters;

    /**
     * Class ZyosSQLImportCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosSQLImportCommand extends Command {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * ZyosSQLImportCommand constructor.
         *
         * @param string|null $name
         * @param Filesystem  $filesystem
         * @param Parameters  $parameters
         */
        function __construct(?string $name = null, Filesystem $filesystem, Parameters $parameters) {

            parent::__construct($name);
            $this->filesystem = $filesystem;
            $this->parameters = $parameters;
            $this->setHidden($this->parameters->hiddenSQL());
            $this->setHelp($this->parameters->translateHelp('zyos.sql.import.help'));
        }

        /**
         * Configure
         *
         * @return void
         */
        protected function configure(): void {

            $this->setName('zyos:sql:import');
            $this->setDescription('Genera el proceso de importar los archivos SQL para la aplicación');
            $this->addArgument('environment', InputArgument::OPTIONAL, 'Entorno a ejecutar', 'dev');
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

            $environment = $input->getArgument('environment');
            $io = new SymfonyStyle($input, $output);

            if ($this->parameters->structure()):
                $this->validateEnable($io, $output, $environment);
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
        private function validateEnable(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            if ($this->parameters->enableSQL()):
                $this->validateLockable($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Desactivado'));
            endif;
        }

        /**
         * Validate is loackable
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         *
         * @return void
         */
        private function validateLockable(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            if ($this->parameters->lockableSQL()):
                $this->validateExistsLockFile($io, $output, $environment);
            else:
                $this->validateEnvironment($io, $output, $environment);
            endif;
        }

        /**
         * Validate exists lock file
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         *
         * @return void
         */
        private function validateExistsLockFile(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            if (!$this->parameters->existsLockFile()):
                $this->validateEnvironment($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Bloqueado'));
            endif;
        }

        /**
         * Validate environment
         *
         * @param SymfonyStyle $io
         * @param string       $environment
         *
         * @return void
         */
        private function validateEnvironment(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            if ($this->parameters->inEnvironment($environment)):
                $this->validateCount($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('Entorno No Valido: %env%', ['%env%' => mb_strtoupper($environment)]));
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
        private function validateCount(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            $params = $this->parameters->getSQL();

            if ($params->count() > 0):
                $this->validateEnableCommands($io, $output, $environment, $params);
            else:
                $io->error($this->parameters->translate('No Hay Comandos para Ejecutar, Cantidad: %count%', ['%count%' => $params->count()]));
            endif;
        }

        /**
         * Get enable commands
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateEnableCommands(SymfonyStyle $io, OutputInterface $output, string $environment, ParameterBag $parameterBag): void {

            $list = array_filter(array_map(function (array $item = []) {
                if (array_key_exists('enable', $item)): return $item['enable'] ? $item : null; endif;
            }, $parameterBag->all()));

            if (count($list) > 0):
                $this->validateEnvironmentCommands($io, $output, $environment, $list);
            else:
                $io->error($this->parameters->translate('No Hay Comandos Activos para Ejecutar'));
            endif;
        }

        /**
         * Get commands environment selected
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param array           $array
         *
         * @return void
         */
        private function validateEnvironmentCommands(SymfonyStyle $io, OutputInterface $output, string $environment, array $array = []): void {

            $list = array_filter(array_map(function (array $item = []) use ($environment) {
                if (array_key_exists('env', $item)): return in_array($environment, $item['env']) ? $item : null; endif;
            }, $array));

            if (count($list) > 0):
                $this->iterateCommands($io, $output, $environment, $list);
            else:
                $io->error($this->parameters->translate('No Hay Comandos con el Entorno Solicitado: %env%', ['%env%' => mb_strtoupper($environment)]));
            endif;
        }

        /**
         * Iterate commands
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param array           $array
         *
         * @return void
         */
        private function iterateCommands(SymfonyStyle $io, OutputInterface $output, string $environment, array $array = []): void {

            foreach ($array AS $item):
                $this->validateCountSQL($io, $output, $environment, $item['files'], $item['connection']);
            endforeach;

            $io->newLine(2);
            $io->text($this->parameters->translate('Se han ejecutado: %count% comandos', ['%count%' => count($array)]));
            $io->success($this->parameters->translate('Se ha finalizado el proceso de ejecución de comandos'));
        }

        /**
         * Validate and count files
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param array           $array
         * @param string|null     $connection
         *
         * @return void
         */
        private function validateCountSQL(SymfonyStyle $io, OutputInterface $output, string $environment, array $array, ?string $connection): void {

            if (count($array) > 0):
                $this->iterateFiles($io, $output, $environment, $array, $connection);
            else:
                $io->error($this->parameters->translate('No hay archivos SQL para cargar'));
            endif;
        }

        /**
         * Iterate files
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param array           $array
         *
         * @return void
         */
        private function iterateFiles(SymfonyStyle $io, OutputInterface $output, string $environment, array $array, ?string $connection): void {

            foreach ($array AS $item):
                $this->executeSQLFile($io, $output, $environment, $item, $connection);
            endforeach;
        }

        /**
         * Execute SQL file
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param string          $name
         *
         * @return void
         */
        private function executeSQLFile(SymfonyStyle $io, OutputInterface $output, string $environment, string $name, ?string $connection): void {

            $file = sprintf('%s/sql/%s', $this->parameters->getPath(), $name);

            if ($this->filesystem->exists($file)):
                $this->executeCommands($output, $environment, $file, $connection);
            else:
                $output->writeln(sprintf('Processing file \'<info>%s</info>\'... <error> %s! </error>', $file, $this->parameters->translate('No Existe') ));
            endif;
        }

        /**
         * Execute commands
         *
         * @param OutputInterface $output
         * @param string          $environment
         * @param string          $command
         * @param array           $arguments
         *
         * @return int
         * @throws \Exception
         */
        private function executeCommands(OutputInterface $output, string $environment, string $file, ?string $connection) {

            $console = $this->getApplication()->find('doctrine:database:import');
            $array = !is_null($connection) ? ['command' => 'doctrine:database:import', 'file' => $file, '--connection' => $connection] :['command' => 'doctrine:database:import', 'file' => $file];
            $input = new ArrayInput($array);

            return $console->run($input, $output);
        }
    }

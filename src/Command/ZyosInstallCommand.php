<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 24/09/20
     * Time: 09:45 AM
     */
    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use ZyosInstallBundle\Service\Parameters;
    use ZyosInstallBundle\Service\Translations;

    /**
     * Class ZyosInstallCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosInstallCommand extends Command {

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * ZyosInstallCommand constructor.
         *
         * @param Parameters   $parameters
         */
        function __construct(Parameters $parameters) {

            parent::__construct(null);
            $this->parameters = $parameters;
            $this->setHidden($this->parameters->existsLockFile());
            $this->setHelp($this->parameters->translateHelp('zyos.install.help'));
        }

        /**
         * Configure
         *
         * @return void
         */
        protected function configure(): void {

            $this->setName('zyos:install');
            $this->setDescription('Genera el proceso de instalación o despliegue de la aplicación');
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
                $this->validateExistsLockFile($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('No es posible crear la estructura en la ruta: %path%', ['%path%' => $this->parameters->getPath()]));
            endif;

            return 0;
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
                $this->validateEnable($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Bloqueado'));
            endif;
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

            if ($this->parameters->enableInstall()):
                $this->validateEnvironment($io, $output, $environment);
            else:
                $io->error($this->parameters->translate('Comando no disponible, Desactivado'));
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

            $params = $this->parameters->getInstall();

            if ($params->count() > 0):
                $this->validateCycle($io, $output, $environment, $params);
            else:
                $io->error($this->parameters->translate('No Hay Comandos para Ejecutar, Cantidad: %count%', ['%count%' => $params->count()]));
            endif;
        }

        /**
         * Valldate cicle command
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param ParameterBag    $parameterBag
         *
         * @return void
         */
        private function validateCycle(SymfonyStyle $io, OutputInterface $output, string $environment, ParameterBag $parameterBag): void {

            $array = array_filter($parameterBag->all(), function ($configuration) {
                return trim($configuration['command']) == $this->getName();
            });

            if (count($array) > 0):
                $io->error($this->parameters->translate('No es posible ejecutar el comando de instalación en el mismo comando'));
            else:
                $this->validateEnableCommands($io, $output, $environment, $parameterBag);
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
                $this->executeCommands($output, $environment, $item['command'], $item['arguments']);
            endforeach;

            $io->newLine(2);
            $io->text($this->parameters->translate('Se han ejecutado: %count% comandos', ['%count%' => count($array)]));
            $io->success($this->parameters->translate('Se ha finalizado el proceso de ejecución de comandos'));

            if ('prod' === $environment):
                $this->createLockFile($io);
            endif;
        }

        /**
         * Create lock file
         *
         * @param SymfonyStyle $io
         *
         * @return void
         */
        private function createLockFile(SymfonyStyle $io): void {

            $isCreated = $this->parameters->createLockFile();

            if($isCreated):
                $io->success($this->parameters->translate('Se ha creado el bloqueo de instalación correctamente'));
            else:
                $io->error($this->parameters->translate('No es posible crear el bloqueo de instalación para entorno de producción'));
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
        private function executeCommands(OutputInterface $output, string $environment, string $command, array $arguments = []) {

            $console = $this->getApplication()->find($command);
            $array = $this->parameters->formatInput($environment, array_merge(['command' => $command], $arguments));
            $input = new ArrayInput($array);

            return $console->run($input, $output);
        }
    }

<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 1/10/20
     * Time: 09:41 PM
     */
    namespace ZyosInstallBundle\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use ZyosInstallBundle\Handlers\ValidationCollections;
    use ZyosInstallBundle\Interfaces\ValidatorInterface;
    use ZyosInstallBundle\Service\Parameters;
    use ZyosInstallBundle\Validations\ExistsValidation;

    /**
     * Class ZyosValidate
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosValidateCommand extends Command {

        /**
         * @var int
         */
        const METHOD_IS = 1;

        /**
         * @var int
         */
        const METHOD_NOT_IS = 2;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var ValidationCollections
         */
        private $validations;

        /**
         * ZyosValidateCommand constructor.
         *
         * @param string|null           $name
         * @param Filesystem            $filesystem
         * @param Parameters            $parameters
         * @param ValidationCollections $validations
         */
        function __construct(?string $name = null, Filesystem $filesystem, Parameters $parameters, ValidationCollections $validations) {

            parent::__construct($name);
            $this->filesystem = $filesystem;
            $this->parameters = $parameters;
            $this->validations = $validations;
            $this->setHidden($this->parameters->hiddenValidation());
            $this->setHelp($this->parameters->translateHelp('zyos.execute.validations.help'));
        }

        /**
         * Configure
         *
         * @return void
         */
        protected function configure(): void {

            $this->setName('zyos:execute:validation');
            $this->setDescription('Genera la ejecución de validaciones para la aplicación');
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

            if ($this->parameters->enableValidation()):
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

            if ($this->parameters->lockableValidation()):
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
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
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
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         *
         * @return void
         */
        private function validateCount(SymfonyStyle $io, OutputInterface $output, string $environment): void {

            $params = $this->parameters->getValidation();

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
                $this->executeValidations($io, $output, $environment, $item['filepath'], $item['validations']);
            endforeach;

            $io->newLine(2);
            $io->text($this->parameters->translate('Se han ejecutado: %count% comandos', ['%count%' => count($array)]));
            $io->success($this->parameters->translate('Se ha finalizado el proceso de ejecución de comandos'));
        }

        /**
         * Execute validations
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $environment
         * @param string          $filepath
         * @param array           $validations
         *
         * @return void
         */
        private function executeValidations(SymfonyStyle $io, OutputInterface $output, string $environment, string $filepath, array $validations = []): void {

            $io->text(sprintf('<comment>*</comment> <info>%s:</info> %s',$this->parameters->translate('Validaciones del Recurso'),$filepath));
            $io->newLine();

            foreach ($validations AS $validation):
                if ($this->validations->has($validation['validation'])):
                    $this->getOutputResult($io, $this->validations->get($validation['validation']), $filepath, $validation['params']);
                else:
                    $this->getOutputError($io, $this->parameters->translate('La validación: %validation% No existe', ['%validation%' => $validation['validation'] ]));
                endif;
            endforeach;

            $io->newLine(1);
            $io->writeln('<comment>--------------------------------------------------------------------------</comment>');
            $io->newLine(1);
        }

        /**
         * Get validation and show result
         *
         * @param SymfonyStyle       $io
         * @param ValidatorInterface $validator
         * @param                    $value
         * @param array              $params
         *
         * @return void
         */
        private function getOutputResult(SymfonyStyle $io, ValidatorInterface $validator, $value, array $params = []) {

            if ($validator->validate($value, $params)):
                $this->getOutputPassed($io);
            else:
                $this->getOutputFailed($io);
            endif;

            $io->writeln(sprintf(' %s', $this->parameters->translate($validator->getDescription()) ));
        }

        /**
         * Show message pass
         *
         * @param SymfonyStyle $io
         *
         * @return void
         */
        private function getOutputPassed(SymfonyStyle $io): void {
            $io->write(sprintf('    <info>%s</info>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'PASSED' : '✔ PASSED'));
        }

        /**
         * Show message failed
         *
         * @param SymfonyStyle $io
         *
         * @return void
         */
        private function getOutputFailed(SymfonyStyle $io): void {
            $io->write(sprintf('    <fg=red>%s</>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'FAILED' : '✕ FAILED'));
        }

        /**
         * Show message ERROR
         *
         * @param SymfonyStyle $io
         * @param string       $text
         *
         * @return void
         */
        private function getOutputError(SymfonyStyle $io, string $text): void {

            $io->write(sprintf('    <fg=white;bg=red>%s </>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'ERROR' : '✕ ERROR'));
            $io->writeln(sprintf(' <fg=white;bg=red;options=bold>%s</>', $text));
        }
    }
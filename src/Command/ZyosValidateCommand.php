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
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use ZyosInstallBundle\Service\Parameters;

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
         * ZyosValidateCommand constructor.
         *
         * @param string|null $name
         * @param Parameters  $parameters
         */
        function __construct(?string $name = null, Filesystem $filesystem, Parameters $parameters) {

            parent::__construct($name);
            $this->filesystem = $filesystem;
            $this->parameters = $parameters;
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

            foreach ($validations AS $validation):
                $array = $this->getValidation($validation);

                if (is_array($array)):
                    $this->executeSingleValidations($io, $output, $filepath, $array);
                else:
                    $io->writeln($this->parameters->translate('La validación: %validation% No existe', ['%validation%' => $validation]));
                endif;
            endforeach;

            $io->newLine(1);
        }

        /**
         * Execute validations
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $filepath
         * @param array           $validations
         *
         * @return void
         */
        private function executeSingleValidations(SymfonyStyle $io, OutputInterface $output, string $filepath, array $validations = []): void {

            if ($this->getFunction($validations['function'], $filepath)):
                $io->write(sprintf('    <info>%s</info>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'PASSED' : '✔ PASSED'));
            else:
                $io->write(sprintf('    <fg=red>%s</>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'FAILED' : '✕ FAILED'));
            endif;

            $io->writeln(sprintf(' %s', $validations['name']));
        }

        /**
         * Return data of validation function
         *
         * @param string|null $validation
         *
         * @return array|null
         */
        private function getValidation(?string $validation): ?array {

            $array = [
                'exists'               => ['name' => $this->parameters->translate('Existencia del recurso'), 'function' => 'exists'],
                'not_exists'           => ['name' => $this->parameters->translate('No debe existir el recurso'), 'function' => 'not_exists'],
                'is_file'              => ['name' => $this->parameters->translate('Es un archivo'), 'function' => 'is_file'],
                'is_not_file'          => ['name' => $this->parameters->translate('No es un archivo'), 'function' => 'is_not_file'],
                'is_dir'               => ['name' => $this->parameters->translate('Es un directorio'), 'function' => 'is_dir'],
                'is_not_dir'           => ['name' => $this->parameters->translate('No es un directorio'), 'function' => 'is_not_dir'],
                'is_link'              => ['name' => $this->parameters->translate('Es un enlace simbolico'), 'function' => 'is_link'],
                'is_not_link'          => ['name' => $this->parameters->translate('No es un enlace simbólico'), 'function' => 'is_not_link'],
                'is_executable'        => ['name' => $this->parameters->translate('Es un ejecutable'), 'function' => 'is_executable'],
                'is_not_executable'    => ['name' => $this->parameters->translate('No es un ejecutable'), 'function' => 'is_not_executable'],
                'is_readable'          => ['name' => $this->parameters->translate('Se puede leer'), 'function' => 'is_readable'],
                'is_not_readable'      => ['name' => $this->parameters->translate('No se debe leer'), 'function' => 'is_not_readable'],
                'is_writable'          => ['name' => $this->parameters->translate('Se puede escribir'), 'function' => 'is_writable'],
                'is_not_writable'      => ['name' => $this->parameters->translate('No se debe escribir'), 'function' => 'is_not_writable'],
                'is_uploaded_file'     => ['name' => $this->parameters->translate('El archivo fue subido mediante HTTP POST'), 'function' => 'is_uploaded_file'],
                'is_not_uploaded_file' => ['name' => $this->parameters->translate('El archivo NO fue subido mediante HTTP POST'), 'function' => 'is_not_uploaded_file']
            ];

            return array_key_exists($validation, $array) ? $array[$validation] : null;
        }

        /**
         * Execute functions and classes for validation
         *
         * @param string|null $function
         * @param             $value
         *
         * @return |null
         */
        private function getFunction(?string $function, $value) {

            $array = [
                'exists'               => ['method' => self::METHOD_IS, 'function' => [$this->filesystem, 'exists']],
                'not_exists'           => ['method' => self::METHOD_NOT_IS, 'function' => [$this->filesystem, 'exists']],
                'is_file'              => ['method' => self::METHOD_IS, 'function' => 'is_file'],
                'is_not_file'          => ['method' => self::METHOD_NOT_IS, 'function' => 'is_file'],
                'is_dir'               => ['method' => self::METHOD_IS, 'function' => 'is_dir'],
                'is_not_dir'           => ['method' => self::METHOD_NOT_IS, 'function' => 'is_dir'],
                'is_link'              => ['method' => self::METHOD_IS, 'function' => 'is_link'],
                'is_not_link'          => ['method' => self::METHOD_NOT_IS, 'function' => 'is_link'],
                'is_executable'        => ['method' => self::METHOD_IS, 'function' => 'is_executable'],
                'is_not_executable'    => ['method' => self::METHOD_NOT_IS, 'function' => 'is_executable'],
                'is_readable'          => ['method' => self::METHOD_IS, 'function' => 'is_readable'],
                'is_not_readable'      => ['method' => self::METHOD_NOT_IS, 'function' => 'is_readable'],
                'is_writable'          => ['method' => self::METHOD_IS, 'function' => 'is_writable'],
                'is_not_writable'      => ['method' => self::METHOD_NOT_IS, 'function' => 'is_writable'],
                'is_uploaded_file'     => ['method' => self::METHOD_IS, 'function' => 'is_uploaded_file'],
                'is_not_uploaded_file' => ['method' => self::METHOD_NOT_IS, 'function' => 'is_uploaded_file']
            ];

            if (array_key_exists($function, $array)):
                if ($array[$function]['method'] == self::METHOD_NOT_IS):
                    return $this->callBooleanNotFunction($array[$function]['function'], [$value]);
                else:
                    return $this->callBooleanFunction($array[$function]['function'], [$value]);
                endif;
            endif;

            return null;
        }

        /**
         * Execute functions and classes for validation
         *
         * @param       $function
         * @param array $params
         *
         * @return mixed
         */
        private function callBooleanFunction($function, array $params = []) {
            return call_user_func_array($function, $params);
        }

        /**
         * Execute functions and classes for validation
         *
         * @param       $function
         * @param array $params
         *
         * @return bool
         */
        private function callBooleanNotFunction($function, array $params = []) {
            return !call_user_func_array($function, $params);
        }
    }
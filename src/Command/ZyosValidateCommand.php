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
    use ZyosInstallBundle\Handlers\ValidationCollections;
    use ZyosInstallBundle\Interfaces\ValidatorInterface;
    use ZyosInstallBundle\Service\Parameters;

    /**
     * Class ZyosValidate
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosValidateCommand extends Command {

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

            $this->getOutputTitleValidation($io, $filepath);
            $this->getLabelInformation($io, $filepath);
            $this->iterateValidations($io, $filepath, $validations);

            $io->newLine(1);
            $io->writeln('<comment>--------------------------------------------------------------------------</comment>');
            $io->newLine(2);
        }

        /**
         * Generate iterations of validations
         *
         * @param SymfonyStyle $io
         * @param string       $filepath
         * @param array        $validations
         *
         * @return void
         */
        private function iterateValidations(SymfonyStyle $io, string $filepath, array $validations = []): void {

            foreach ($validations AS $validation):
                $this->getHasValidations($io, $filepath, $validation['validation'], $validation['params']);
            endforeach;
        }

        /**
         * Validate has validation
         *
         * @param SymfonyStyle $io
         * @param string       $filepath
         * @param string       $validation
         * @param array        $params
         *
         * @return void
         */
        private function getHasValidations(SymfonyStyle $io, string $filepath, string $validation, array $params = []): void {

            if ($this->validations->has($validation)):
                $this->getValidateFilepath($io, $this->validations->get($validation), $filepath, $params);
            else:
                $io->write('    ');
                $this->getOutputError($io, 'La validación: %validation% No existe', [ '%validation%' => $validation ]);
            endif;
        }

        /**
         * Get validation filepath
         *
         * @param SymfonyStyle       $io
         * @param ValidatorInterface $validator
         * @param string             $filepath
         * @param array              $params
         *
         * @return void
         */
        private function getValidateFilepath(SymfonyStyle $io, ValidatorInterface $validator, string $filepath, array $params = []): void {

            $io->write('    ');

            if ($validator->validate($filepath, $params)):
                $this->getOutputPassed($io);
                $io->write(' ');
                $io->writeln( $this->parameters->translate($validator->getDescription()) );
            else:
                $this->getOutputFailed($io);
                $io->write(' ');
                $io->writeln(sprintf('<fg=yellow>%s</>', $this->parameters->translate($validator->getDescription()) ));
            endif;
        }

        /**
         * Show message pass
         *
         * @param SymfonyStyle $io
         *
         * @return void
         */
        private function getOutputPassed(SymfonyStyle $io): void {
            $io->write(sprintf('<info>%s</info>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'PASSED' : '✔ PASSED'));
        }

        /**
         * Show message failed
         *
         * @param SymfonyStyle $io
         *
         * @return void
         */
        private function getOutputFailed(SymfonyStyle $io): void {
            $io->write(sprintf('<fg=red>%s</>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'FAILED' : '✕ FAILED'));
        }

        /**
         * Show message ERROR
         *
         * @param SymfonyStyle $io
         * @param string       $text
         * @param array        $params
         *
         * @return void
         */
        private function getOutputError(SymfonyStyle $io, string $text, array $params = []): void {

            $io->write(sprintf('<fg=white;bg=red>%s </>', defined('PHP_WINDOWS_VERSION_BUILD') ? 'ERROR' : '✕ ERROR'));
            $io->write(' ');
            $io->writeln(sprintf('<fg=white;bg=red;options=bold>%s</>', $this->parameters->translate($text, $params) ));
        }

        /**
         * Show information labels
         *
         * @param SymfonyStyle $io
         * @param string       $filepath
         *
         * @return void
         */
        private function getLabelInformation(SymfonyStyle $io, string $filepath): void {

            if ($this->filesystem->exists($filepath)):

                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Tipo');
                $this->getOutputLabelGreen($io, $this->parameters->getTypeFilepath($filepath));
                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Permisos');
                $this->getOutputLabelYellow($io, substr(sprintf('%o', fileperms($filepath)), -4));
                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Permisos');
                $this->getOutputLabelYellow($io, $this->getFormatPermission($filepath));
                $io->newLine(2);
                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Fecha de Creación');
                $this->getOutputLabelYellow($io, date("F d Y H:i:s A", filectime($filepath)));
            else:

                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Tipo');
                $this->getOutputLabelUnknown($io, 'Desconocido');
                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Permisos');
                $this->getOutputLabelUnknown($io, 'Desconocido');
                $io->write('  ');
                $this->getOutputLabelCyan($io, 'Permisos');
                $this->getOutputLabelUnknown($io, 'Desconocido');
            endif;

            $io->newLine(3);
        }

        /**
         * Set title of validation
         *
         * @param SymfonyStyle $io
         * @param string       $filepath
         *
         * @return void
         */
        private function getOutputTitleValidation(SymfonyStyle $io, string $filepath): void {
            $io->writeln(sprintf('<comment>*</comment> <info>%s:</info> %s', $this->parameters->translate('Validaciones del Recurso'), $filepath));
            $io->newLine();
        }

        /**
         * Get label cyan
         *
         * @param SymfonyStyle $io
         * @param string       $text
         *
         * @return void
         */
        private function getOutputLabelCyan(SymfonyStyle $io, string $text): void {
            $io->write(sprintf('<bg=cyan;options=bold> %s </>', $this->parameters->translate($text) ));
        }

        /**
         * Get label green
         *
         * @param SymfonyStyle $io
         * @param string       $text
         *
         * @return void
         */
        private function getOutputLabelGreen(SymfonyStyle $io, string $text): void {
            $io->write(sprintf('<bg=green;options=bold> %s </>', $this->parameters->translate($text) ));
        }

        /**
         * Get label green
         *
         * @param SymfonyStyle $io
         * @param string       $text
         *
         * @return void
         */
        private function getOutputLabelYellow(SymfonyStyle $io, string $text): void {
            $io->write(sprintf('<bg=yellow;fg=black> %s </>', $this->parameters->translate($text) ));
        }

        /**
         * Get label unknown
         *
         * @param SymfonyStyle $io
         * @param string       $text
         *
         * @return void
         */
        private function getOutputLabelUnknown(SymfonyStyle $io, string $text): void {
            $io->write(sprintf('<bg=red;options=bold,blink> %s </>', $this->parameters->translate($text) ));
        }

        /**
         * Get string permission of filepath
         *
         * @param string $filepath
         *
         * @return string
         */
        private function getFormatPermission(string $filepath): string {

            $perms = fileperms($filepath);

            switch ($perms & 0xF000):
                case 0xC000: $info = 's'; // socket
                    break;
                case 0xA000: $info = 'l'; // symbolic link
                    break;
                case 0x8000: $info = 'r'; // regular
                    break;
                case 0x6000: $info = 'b'; // block special
                    break;
                case 0x4000: $info = 'd'; // directory
                    break;
                case 0x2000: $info = 'c'; // character special
                    break;
                case 0x1000: $info = 'p'; // FIFO pipe
                    break;
                default: $info = 'u'; // unknown
            endswitch;

            // Owner
            $info .= (($perms & 0x0100) ? 'r' : '-');
            $info .= (($perms & 0x0080) ? 'w' : '-');
            $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

            // Group
            $info .= (($perms & 0x0020) ? 'r' : '-');
            $info .= (($perms & 0x0010) ? 'w' : '-');
            $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

            // World
            $info .= (($perms & 0x0004) ? 'r' : '-');
            $info .= (($perms & 0x0002) ? 'w' : '-');
            $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

            return $info;
        }
    }
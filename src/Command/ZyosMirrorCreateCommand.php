<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 29/09/20
     * Time: 10:42 PM
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
     * Class ZyosMirrorCreateCommand
     *
     * @package ZyosInstallBundle\Command
     */
    class ZyosMirrorCreateCommand extends Command {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * ZyosMirrorCreateCommand constructor.
         *
         * @param Filesystem  $filesystem
         * @param Parameters  $parameters
         */
        function __construct(Filesystem $filesystem, Parameters $parameters) {

            parent::__construct(null);
            $this->filesystem = $filesystem;
            $this->parameters = $parameters;
            $this->setHidden($this->parameters->hiddenMirror());
            $this->setHelp($this->parameters->translateHelp('zyos.mirror.create.help'));
        }

        /**
         * Configure
         *
         * @return void
         */
        protected function configure(): void {

            $this->setName('zyos:create:mirror');
            $this->setDescription('Genera la creación de mirror de directorios para la aplicación');
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

            if ($this->parameters->enableMirror()):
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

            if ($this->parameters->lockableMirror()):
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

            $params = $this->parameters->getMirror();

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

            $io->newLine(1);

            foreach ($array AS $item):
                $this->validateMirror($io, $output, $item['origin'], $item['destination']);
            endforeach;

            $io->newLine(2);
            $io->text($this->parameters->translate('Se han ejecutado: %count% comandos', ['%count%' => count($array)]));
            $io->success($this->parameters->translate('Se ha finalizado el proceso de ejecución de comandos'));
        }

        /**
         * Validate symlink
         *
         * @param SymfonyStyle    $io
         * @param OutputInterface $output
         * @param string          $origin
         * @param string          $destination
         *
         * @return void
         */
        private function validateMirror(SymfonyStyle $io, OutputInterface $output, string $origin, string $destination): void {

            $output->write(sprintf('Processing %s \'<info>%s</info>\'... ', $this->parameters->translate('Destino'), $destination));

            if (!$this->filesystem->exists($origin)):
                $output->writeln(sprintf('<error>Error: %s</error>', $this->parameters->translate('El directorio o archivo de origen no existe')));
            else:
                try {
                    if (is_file($origin)):
                        $this->filesystem->copy($origin, $destination, true);
                    else:
                        $this->filesystem->mirror($origin, $destination);
                    endif;

                    $output->writeln('OK!');
                }
                catch (ExceptionInterface $exceptionInterface) {
                    $output->writeln(sprintf('<error>Error: %s</error>', $this->parameters->translate('El mirror no es posible crearlo')));
                }
                catch (IOExceptionInterface $IOExceptionInterface) {
                    $output->writeln(sprintf('<error>Error: %s</error>', $this->parameters->translate('El mirror no es posible crearlo')));
                }
                catch (IOException $IOException) {
                    $output->writeln(sprintf('<error>Error: %s</error>', $this->parameters->translate('El mirror no es posible crearlo')));
                }
                catch (\Exception $exception) {
                    $output->writeln(sprintf('<error>Error: %s</error>', $this->parameters->translate('El mirror no es posible crearlo')));
                }
            endif;
            $io->newLine();
        }
    }

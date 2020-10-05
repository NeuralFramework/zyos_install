<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 24/09/20
     * Time: 08:32 AM
     */
    namespace ZyosInstallBundle\Service;

    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpKernel\KernelInterface;

    /**
     * Class ParameterBag
     *
     * @package ZyosInstallBundle\Service
     */
    class Parameters {

        /**
         * @var ParameterBagInterface
         */
        private $parameterBag;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var KernelInterface
         */
        private $kernel;

        /**
         * @var Translations
         */
        private $translations;

        /**
         * Parameters constructor.
         *
         * @param ParameterBagInterface $parameterBag
         * @param Filesystem            $filesystem
         * @param KernelInterface       $kernel
         */
        function __construct(ParameterBagInterface $parameterBag, Filesystem $filesystem, KernelInterface $kernel, Translations $translations) {

            $this->parameterBag = $parameterBag;
            $this->filesystem = $filesystem;
            $this->kernel = $kernel;
            $this->translations = $translations;
        }

        /**
         * Get locale for translations
         *
         * @return string
         */
        public function getTranslation(): string {
            return $this->parameterBag->get('zyos_install.translation');
        }

        /**
         * Get symfony environment
         *
         * @return string
         */
        public function getEnvironment(): string {
            return $this->kernel->getEnvironment();
        }

        /**
         * Get path
         *
         * @return string
         */
        public function getPath(): string {
            return $this->parameterBag->get('zyos_install.path');
        }

        /**
         * Validate exists path
         *
         * @return bool
         */
        public function existsPath(): bool {
            return $this->filesystem->exists($this->getPath());
        }

        /**
         * Validate is environment in
         *
         * @param string $environment
         *
         * @return bool
         */
        public function inEnvironment(string $environment): bool {
            return in_array($environment, $this->parameterBag->get('zyos_install.environments'));
        }

        /**
         * Exists file lock
         *
         * @return bool
         */
        public function existsLockFile(): bool {
            return $this->filesystem->exists($this->getLockFile());
        }

        /**
         * Get lock file path
         *
         * @return string
         */
        public function getLockFile(): string {
            return sprintf('%s/lock.lock', $this->getPath());
        }

        /**
         * Create lock file
         *
         * @return bool
         */
        public function createLockFile(): bool {

            $this->filesystem->dumpFile($this->getLockFile(), date('Y-m-d H:i:s'));
            return $this->existsLockFile();
        }

        /**
         * Get is enable install
         *
         * @return bool
         */
        public function enableInstall(): bool {
            return $this->parameterBag->get('zyos_install.install.enable');
        }

        /**
         * Get install commands
         *
         * @return ParameterBag
         */
        public function getInstall(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.install.commands'));
        }

        /**
         * Get is enable symlink
         *
         * @return bool
         */
        public function enableSymlink(): bool {
            return $this->parameterBag->get('zyos_install.symlink.enable');
        }

        /**
         * get is lockable symlink
         *
         * @return bool
         */
        public function lockableSymlink(): bool {
            return $this->parameterBag->get('zyos_install.symlink.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenSymlink(): bool {
            
            if ($this->enableSymlink()):
                return $this->lockableSymlink() ? $this->existsLockFile() : false;
            else:
                return true;
            endif;
        }

        /**
         * Get install commands
         *
         * @return ParameterBag
         */
        public function getSymlink(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.symlink.commands'));
        }

        /**
         * Get is enable mirror
         *
         * @return bool
         */
        public function enableMirror(): bool {
            return $this->parameterBag->get('zyos_install.mirror.enable');
        }

        /**
         * get is lockable mirror
         *
         * @return bool
         */
        public function lockableMirror(): bool {
            return $this->parameterBag->get('zyos_install.mirror.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenMirror(): bool {

            if ($this->enableMirror()):
                return $this->lockableMirror() ? $this->existsLockFile() : false;
            else:
                return true;
            endif;
        }

        /**
         * Get mirror commands
         *
         * @return ParameterBag
         */
        public function getMirror(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.mirror.commands'));
        }

        /**
         * Get is enable SQL
         *
         * @return bool
         */
        public function enableSQL(): bool {
            return $this->parameterBag->get('zyos_install.sql.enable');
        }

        /**
         * Get is loackable SQL
         *
         * @return bool
         */
        public function lockableSQL(): bool {
            return $this->parameterBag->get('zyos_install.sql.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenSQL(): bool {

            if ($this->enableSQL()):
                return $this->lockableSQL() ? $this->existsLockFile() : false;
            else:
                return true;
            endif;
        }

        /**
         * Get SQL commands
         *
         * @return ParameterBag
         */
        public function getSQL(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.sql.commands'));
        }

        /**
         * Get is enable CLI
         *
         * @return bool
         */
        public function enableCli(): bool {
            return $this->parameterBag->get('zyos_install.cli.enable');
        }

        /**
         * Get is loackable CLI
         *
         * @return bool
         */
        public function lockableCli(): bool {
            return $this->parameterBag->get('zyos_install.cli.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenCli(): bool {

            if ($this->enableCli()):
                return $this->lockableCli() ? $this->existsLockFile() : false;
            else:
                return true;
            endif;
        }

        /**
         * Get CLI commands
         *
         * @return ParameterBag
         */
        public function getCli(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.cli.commands'));
        }

        /**
         * Get is enable Validation
         *
         * @return bool
         */
        public function enableValidation(): bool {
            return $this->parameterBag->get('zyos_install.validation.enable');
        }

        /**
         * Get is loackable Validation
         *
         * @return bool
         */
        public function lockableValidation(): bool {
            return $this->parameterBag->get('zyos_install.validation.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenValidation(): bool {

            if ($this->enableValidation()):
                return $this->lockableValidation() ? $this->existsLockFile() : false;
            else:
                return true;
            endif;
        }

        /**
         * Get Validation commands
         *
         * @return ParameterBag
         */
        public function getValidation(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.validation.commands'));
        }

        /**
         * Get is enable dump
         *
         * @return bool
         */
        public function enableExport(): bool {
            return $this->parameterBag->get('zyos_install.export.enable');
        }

        /**
         * Get is loackable dump
         *
         * @return bool
         */
        public function lockableExport(): bool {
            return $this->parameterBag->get('zyos_install.export.lockable');
        }

        /**
         * Get hidden command
         *
         * @return bool
         */
        public function hiddenExport(): bool {
            return !$this->enableExport();
        }

        /**
         * Get dump commands
         *
         * @return ParameterBag
         */
        public function getExport(): ParameterBag {
            return new ParameterBag($this->parameterBag->get('zyos_install.export.commands'));
        }

        /**
         * Format input arguments
         *
         * @param string $environment
         * @param array  $input
         *
         * @return array
         */
        public function formatInput(string $environment, array $input = []): array {

            $list = [];
            foreach ($input AS $key => $value):
                $list[$this->formatInputString($environment, $key)] = $this->formatInputString($environment, $value);
            endforeach;
            return $list;
        }

        /**
         * Format text
         *
         * @param string $environment
         * @param        $value
         *
         * @return string
         */
        public function formatInputString(string $environment, $value) {

            if (is_array($value)):
                return $this->formatInput($environment, $value);
            elseif (is_string($value)):
                return strtr($value, ['{{env}}' => $environment]);
            else:
                return $value;
            endif;
        }

        /**
         * Get translate
         *
         * @param string $text
         * @param array  $params
         *
         * @return string
         */
        public function translate(string $text, array $params = []): string {
            return $this->translations->translate($text, $params, $this->getTranslation());
        }

        /**
         * Get translate
         *
         * @param string $text
         * @param array  $params
         *
         * @return string
         */
        public function translateHelp(string $text, array $params = []): string {
            return $this->translations->translateHelp($text, $params, $this->getTranslation());
        }

        /**
         * Validate structure and create directories
         *
         * @return bool
         */
        public function structure(): bool {

            $directory = $this->getPath();
            if (!$this->existsPath()): $this->filesystem->mkdir($directory); endif;

            $dump = sprintf('%s/dump', $directory);
            if (!$this->filesystem->exists($dump)): $this->filesystem->mkdir($dump); endif;

            $sql = sprintf('%s/sql', $directory);
            if (!$this->filesystem->exists($sql)): $this->filesystem->mkdir($sql); endif;

            return $this->existsPath();
        }
    }
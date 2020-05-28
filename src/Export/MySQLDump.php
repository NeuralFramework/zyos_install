<?php

    namespace ZyosInstallBundle\Export;

    use ZyosInstallBundle\ParameterBag\MethodBag;

    /**
     * Class MySQLDump
     *
     * @package ZyosInstallBundle\Export
     */
    class MySQLDump {

        /**
         * @var MethodBag
         */
        private $parameters;

        /**
         * @var string
         */
        private $path;

        /**
         * MySQLDump constructor.
         *
         * @param MethodBag $parameters
         * @param string $path
         */
        function __construct(MethodBag $parameters, string $path) {

            $this->parameters = $parameters;
            $this->path = $path;
        }

        /**
         * @return array
         */
        public function create(): array {

            $list[] = 'mysqldump';
            $list[] = $this->getHost();
            $list[] = $this->getPort();
            $list[] = $this->getUser();
            $list[] = $this->getPassword();
            $list[] = $this->getExtendedInsert();
            $list[] = $this->getNotCreateDatabase();
            $list[] = $this->getNotCreateInfo();
            $list[] = $this->getNoData();
            $list[] = $this->getDropTables();
            $list[] = $this->getLockTable();
            $list[] = $this->getFilename();
            $list[] = '--databases';
            $list[] = $this->parameters->get('database');

            return array_filter($list);
        }

        /**
         * @return string
         */
        private function getUser(): string {
            return sprintf('--user="%s"', $this->parameters->get('username'));
        }

        /**
         * @return string
         */
        private function getPassword(): string {
            return sprintf('--password="%s"', $this->parameters->get('password'));
        }

        /**
         * @return string
         */
        private function getHost(): string {
            return sprintf('--host="%s"', $this->parameters->get('host'));
        }

        /**
         * @return string
         */
        private function getPort(): string {
            return sprintf('--port="%s"', $this->parameters->get('port'));
        }

        /**
         * @return string
         */
        private function getExtendedInsert(): string {
            return sprintf('--extended-insert=%s', $this->parameters->getBoolean('extended_insert') ? 'true' : 'false');
        }

        /**
         * @return string
         */
        private function getDropTables(): string {
            return sprintf('--add-drop-table=%s', $this->parameters->getBoolean('drop_tables') ? 'true' : 'false');
        }

        /**
         * @return string
         */
        private function getLockTable(): string {
            return sprintf('--lock-tables=%s', $this->parameters->getBoolean('lock_tables') ? 'true' : 'false');
        }

        /**
         * @return string|null
         */
        private function getNotCreateDatabase(): ?string {

            if (!$this->parameters->getBoolean('all')):
                return sprintf('--no-create-db=%s', $this->parameters->getBoolean('no_create_database') ? 'true' : 'false');
            endif;
            return null;
        }

        /**
         * @return string|null
         */
        private function getNotCreateInfo(): ?string {

            if (!$this->parameters->getBoolean('all')):
                return sprintf('--no-create-info=%s', $this->parameters->getBoolean('no_create_info') ? 'true' : 'false');
            endif;
            return null;
        }

        /**
         * @return string|null
         */
        private function getNoData(): ?string {

            if (!$this->parameters->getBoolean('all')):
                return sprintf('--no-data=%s', $this->parameters->getBoolean('no_data') ? 'true' : 'false');
            endif;
            return null;
        }

        /**
         * @return string
         */
        private function getFilename(): string {
            return sprintf('--result-file="%s/%s"', $this->path, $this->getName());
        }

        /**
         * @return string
         */
        public function getName(): string {

            if ($this->parameters->getBoolean('result_file')):
                return $this->parameters->get('result_file');
            endif;
            return sprintf('%s_%s.sql', $this->parameters->get('name'), date('Ymd_His'));
        }
    }
<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 3/10/20
     * Time: 11:53 PM
     */
    namespace ZyosInstallBundle\Export;

    use Symfony\Component\HttpFoundation\ParameterBag;

    /**
     * Class Manager
     *
     * @package App\Export
     */
    class Manager {

        /**
         * @var ParameterBag
         */
        private $params;

        /**
         * @var string
         */
        private $path;

        /**
         * Manager constructor.
         *
         * @param ParameterBag $params
         */
        function __construct(ParameterBag $params, string $path) {

            $this->params = $params;
            $this->path = $path;
        }

        /**
         * Get client execute
         *
         * @return string|null
         */
        public function getClient(): ?string {
            return $this->params->get('client', null);
        }

        /**
         * Validate and return string values
         *
         * @param string $key
         * @param string $template
         *
         * @return string|null
         */
        private function validateString(string $key, string $template): ?string {

            if ($this->params->has($key)):
                return !is_null($this->params->get($key)) ? sprintf($template, $this->params->get($key)) : null;
            endif;

            return null;
        }

        /**
         * Get string param for execute
         *
         * @param string $key
         * @param string $template
         *
         * @return string|null
         */
        private function validateBoolean(string $key, string $template): ?string {
            return $this->params->has($key) ? sprintf($template, $this->params->getBoolean($key) ? 'true' : 'false') : null;
        }

        /**
         * Get string param for execute
         *
         * @param string $key
         * @param string $template
         *
         * @return string|null
         */
        private function validateArray(string $key, string $template): ?string {

            if ($this->params->has($key)):
                $array = array_values(array_filter($this->params->get($key)));
                if (count($array) > 0):
                    return implode(' ', array_map(function ($value) use ($template) {
                        return sprintf($template, $value);
                    }, $array));
                endif;
            endif;

            return null;
        }

        /**
         * Get result file
         *
         * @return string
         */
        private function validateResultFile(): string {
            return sprintf('--result-file="%s"', $this->getFile());
        }

        /**
         * Generate filename to export database
         *
         * @param string|null $name
         * @param string      $configuration
         *
         * @return string
         */
        private function getFile(): string {

            if (is_null($this->params->get('result-file'))):
                return sprintf('%s/dump/%s_%s.sql', $this->path, $this->params->get('configuration_name'), date('Y_m_d_H_i_s'));
            else:
                return sprintf('%s/dump/%s', $this->path, $this->params->get('result-file'));
            endif;
        }

        /**
         * Get command to execute
         *
         * @return string|null
         */
        public function getCommand(): ?string {

            if ('mysqldump' == $this->getClient()):
                return implode(' ', $this->getMysqldump());
            endif;

            return null;
        }

        /**
         * Create sentence of command
         *
         * @return array
         */
        private function getMysqldump(): array {

            $list[] = 'mysqldump';
            $list[] = $this->validateString('host', '--host="%s"');
            $list[] = $this->validateString('port', '--port="%s"');
            $list[] = $this->validateString('username', '--user="%s"');
            $list[] = $this->validateString('password', '--password="%s"');
            $list[] = $this->validateBoolean('extended-insert', '--extended-insert=%s');
            $list[] = $this->validateBoolean('no-create-database', '--no-create-db=%s');
            $list[] = $this->validateBoolean('no-create-tables', '--no-create-info=%s');
            $list[] = $this->validateBoolean('no-create-insert', '--no-data=%s');
            $list[] = $this->validateBoolean('no-create-lock-tables', '--lock-tables=%s');
            $list[] = $this->validateArray('ignore-table', '--ignore-table="'.$this->params->get('database').'.%s"');
            $list[] = $this->validateResultFile();
            $list[] = $this->validateString('database', '%s');
            $list[] = $this->validateArray('table', '"%s"');

            return array_filter($list);
        }
    }
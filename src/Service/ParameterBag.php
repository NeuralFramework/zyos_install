<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 09:42 AM
     */
    namespace ZyosInstallBundle\Service;

    /**
     * Class ParameterBag
     *
     * @package ZyosInstallBundle\Service
     */
    class ParameterBag implements \IteratorAggregate, \Countable {

        /**
         * @var array
         */
        protected $parameters;

        /**
         * ParameterBag constructor.
         *
         * @param array $parameters
         */
        function __construct(array $parameters = []) {
            $this->parameters = $parameters;
        }

        /**
         * Get all parameters
         *
         * @return array
         */
        public function all(): array {
            return $this->parameters;
        }

        /**
         * Get keys of parameters
         *
         * @return array
         */
        public function keys(): array {
            return array_keys($this->parameters);
        }

        /**
         * Get parameter by key
         *
         * @param string $key
         * @param null   $default
         *
         * @return mixed|null
         */
        public function get(string $key, $default = null) {
            return $this->has($key) ? $this->parameters[$key] : $default;
        }

        /**
         * Set parameters
         *
         * @param string $key
         * @param        $value
         *
         * @return void
         */
        public function set(string $key, $value): void {
            $this->parameters[$key] = $value;
        }

        /**
         * Exists parameter key
         *
         * @param string $key
         *
         * @return bool
         */
        public function has(string $key): bool {
            return array_key_exists($key, $this->parameters);
        }

        /**
         * Remove parameter
         *
         * @param string $key
         *
         * @return void
         */
        public function remove(string $key): void {
            unset($this->parameters[$key]);
        }

        /**
         * Get alphabetic characters from parameter
         *
         * @param string $key
         * @param string $default
         *
         * @return string|string[]|null
         */
        public function getAlpha(string $key, string $default = '') {
            return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
        }

        /**
         * Get alphabetic characters and digits of parameter
         *
         * @param string $key
         * @param string $default
         *
         * @return string|string[]|null
         */
        public function getAlnum(string $key, string $default = '') {
            return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
        }

        /**
         * Returns the digits of the parameter value.
         *
         * @param string $key
         * @param string $default
         *
         * @return string The filtered value
         */
        public function getDigits(string $key, string $default = '') {
            return str_replace(['-', '+'], '', $this->filter($key, $default, \FILTER_SANITIZE_NUMBER_INT));
        }

        /**
         * Returns the parameter value converted to integer.
         *
         * @param string $key
         * @param int    $default
         *
         * @return int The filtered value
         */
        public function getInt(string $key, int $default = 0): int {
            return (int) $this->get($key, $default);
        }

        /**
         * Returns the parameter value converted to boolean.
         *
         * @param string $key
         * @param bool   $default
         *
         * @return bool The filtered value
         */
        public function getBoolean(string $key, bool $default = false): bool {
            return $this->filter($key, $default, \FILTER_VALIDATE_BOOLEAN);
        }

        /**
         * Filter parameter
         *
         * @param string $key
         * @param null   $default
         * @param int    $filter
         * @param array  $options
         *
         * @return mixed
         */
        public function filter(string $key, $default = null, int $filter = \FILTER_DEFAULT, $options = []) {

            $value = $this->get($key, $default);

            if (!\is_array($options) && $options):
                $options = ['flags' => $options];
            endif;

            if (\is_array($value) && !isset($options['flags'])):
                $options['flags'] = \FILTER_REQUIRE_ARRAY;
            endif;

            return filter_var($value, $filter, $options);
        }

        /**
         * Retrieve an external iterator
         *
         * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return \ArrayIterator An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         * @since 5.0.0
         */
        public function getIterator() {
            return new \ArrayIterator($this->parameters);
        }

        /**
         * Count elements of an object
         *
         * @link  https://php.net/manual/en/countable.count.php
         * @return int The custom count as an integer.
         * </p>
         * <p>
         * The return value is cast to an integer.
         * @since 5.1.0
         */
        public function count() {
            return count($this->parameters);
        }

        /**
         * Validate if value of parameter is array
         *
         * @param string $key
         *
         * @return bool
         */
        public function isArray(string $key): bool {
            return is_array($this->get($key));
        }

        /**
         * Validate if value of parameter is string
         *
         * @param string $key
         *
         * @return bool
         */
        public function isString(string $key): bool {
            return is_string($this->get($key));
        }

        /**
         * Validate if value of parameter is integer
         *
         * @param string $key
         *
         * @return bool
         */
        public function isInteger(string $key): bool {
            return is_integer($this->get($key));
        }

        /**
         * Validate if value of parameter is float
         *
         * @param string $key
         *
         * @return bool
         */
        public function isFloat(string $key): bool {
            return is_float($this->get($key));
        }

        /**
         * Validate if value of parameter is double
         *
         * @param string $key
         *
         * @return bool
         */
        public function isDouble(string $key): bool {
            return is_double($this->get($key));
        }

        /**
         * Validate if value of parameter is null
         *
         * @param string $key
         *
         * @return bool
         */
        public function isNull(string $key): bool {
            return is_null($this->get($key));
        }

        /**
         * Validate if value of parameter is boolean
         *
         * @param string $key
         *
         * @return bool
         */
        public function isBoolean(string $key): bool {
            return is_bool($this->get($key));
        }

        /**
         * Validate value in parameter array
         * @param string $key
         * @param null   $value
         *
         * @return bool
         */
        public function in(string $key, $value = null): bool {
            return in_array($value, $this->get($key, []));
        }

        /**
         * Get instance from data array
         *
         * @param string $key
         *
         * @return ParameterBag
         */
        public function self(string $key): self {
            return new self($this->get($key, []));
        }

        /**
         * Return merge array data parameter
         * with array data custom
         *
         * @param string $key
         * @param array  $array
         *
         * @return array
         */
        public function merge(string $key, array $array = []): array {
            return array_merge($this->get($key, []), $array);
        }

        /**
         * Array filter of parameter
         *
         * @param string   $key
         * @param callable $callback
         *
         * @return array
         */
        public function arrayFilter(string $key, callable $callback): array {
            return array_filter($this->get($key, []), $callback);
        }

        /**
         * Array filter of parameter
         *
         * @param string   $key
         * @param callable $callback
         *
         * @return ParameterBag
         */
        public function selfArrayFilter(string $key, callable $callback): self {
            return new self($this->arrayFilter($key, $callback));
        }
    }
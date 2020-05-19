<?php

    namespace ZyosInstallBundle\Services;

    use ZyosInstallBundle\MethodBag;

    /**
     * Class Commands
     *
     * @package ZyosInstallBundle\Services
     */
    class Commands extends MethodBag {

        /**
         * Commands constructor.
         */
        public function __construct() {

            parent::__construct([
                'assets:install' => ['command' => 'assets:install', 'params' => ['target' =>  'public', '--symlink' => true, '--relative' => true]],
                'cache:clear' => ['command' => 'cache:clear', 'params' => ['--no-warmup' => true, '--no-optional-warmers' => true]],
                'doctrine:database:create' => ['command' => 'doctrine:database:create', 'params' => ['--if-not-exists' => true]],
                'doctrine:database:drop' => ['command' => 'doctrine:database:drop', 'params' => ['--if-exists' => true, '--force' => true]],
                'doctrine:database:import' => ['command' => 'doctrine:database:import', 'params' => [] ],
                'doctrine:schema:create' => ['command' => 'doctrine:schema:create', 'params' => [] ],
                'doctrine:schema:drop' => ['command' => 'doctrine:schema:drop', 'params' => ['--force' => true, '--dump-sql' => true]],
                'doctrine:schema:update' => ['command' => 'doctrine:schema:update', 'params' => ['--force' => true, '--dump-sql' => true]],
                'doctrine:schema:validate' => ['command' => 'doctrine:schema:validate', 'params' => [] ],
                'doctrine:fixtures:load' => ['command' => 'doctrine:fixtures:load', 'params' => ['--append' => true] ],
                'zyos:sql:import' => ['command' => 'zyos:sql:import', 'params' => [] ],
                'zyos:create:mirror' => ['command' => 'zyos:create:mirror', 'params' => [] ],
                'zyos:create:symlink' => ['command' => 'zyos:create:symlink', 'params' => [] ],
            ]);
        }
    }
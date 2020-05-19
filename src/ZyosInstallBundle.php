<?php

    namespace ZyosInstallBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use ZyosInstallBundle\DependencyInjection\ZyosInstallExtension;

    /**
     * Class ZyosInstallBundle
     *
     * @package ZyosInstallBundle
     */
    class ZyosInstallBundle extends Bundle {

        /**
         * Validate extension and load
         *
         * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface|ZyosInstallExtension|null
         */
        public function getContainerExtension() {

            if (null === $this->extension):
                $this->extension = new ZyosInstallExtension();
            endif;

            return $this->extension;
        }

    }
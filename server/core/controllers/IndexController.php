<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class IndexController extends BaseController {
        /** @var bool */
        protected $_withoutChecking = true;

        /**
         * Основная страница
         */
        public function indexAction() {
            $vkParams = $this->_globals->getParam('vk');

            $this->loadView('index', array(
               'vk'         => $vkParams
            ));
        }
    }

<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class IndexController extends BaseController {
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

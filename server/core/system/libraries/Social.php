<?php
    /*
     * Фасад для работы с соцсетями
     */
    class Social {
        /** @var Request */
        protected $_request = null;
        /** @var int */
        private $_userId;
        /** @var string */
        private $_platform;
        /** @var Vkapi */
        private $_platformClass;
        /** @var Globals */
        private $_globals;

        public function __construct() {
            $this->_globals  = Globals::init();
            $this->_platform = $this->_globals->getPlatform();

            switch ($this->_platform) {
                case 'vk':
                    $this->_platformClass = new Vkapi($this->_globals->getParam('vk'), $this->getUserId());
                break;
            }
        }

        /**
         * ID пользователя в FB
         * @return string
         */
        private function vkUserId() {
            $vkConfig = $this->_globals->getParam('vk');
            $uid = $this->getRequest()->getParam('viewer_id', false);
            $authKey = $this->getRequest()->getParam('auth_key', false);

            if(!$authKey || !$uid || md5($vkConfig['app_id'] . '_' . $uid . '_' . $vkConfig['api_secret']) != $authKey) {
                $uid = false;
            }
            return $uid;
        }

        /**
         * Id пользователя
         * @return int|string
         */
        public function getUserId() {
            if(!$this->_userId) {
                switch ($this->_platform) {
                    case 'vk':
                        $this->_userId = $this->vkUserId();
                        break;
                }
            }
            return $this->_userId;
        }

        /**
         * Профиль пользователя в соцсети
         * @return array|bool
         */
        public function getProfile() {
            switch ($this->_platform) {
                case 'vk':
                    if(!$this->getUserId()) {
                        return false;
                    } else {
                        return $this->_platformClass->api('user.get');
                    }
                    break;
            }
        }

        /**
         * Получить параметры запроса
         * @return Request
         */
        public function getRequest() {
            if ($this->_request == null) {
                $this->_request = new Request();
            }

            return $this->_request;
        }
    }


<?php
    /*
     * Фасад для работы с соцсетями
     */
    class Social {
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
            if(isset($_REQUEST['access_token']) && isset($_REQUEST['uid']) && md5($vkConfig['app_id'] . '_' . $_REQUEST['uid'] . '_' . $vkConfig['vk']['api_secret']) == $_REQUEST['access_token']) {
                $uid = $_REQUEST['uid'];
            }
            else {
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
         * Список друзей
         * @return array|bool
         */
        public function getFriendList() {
            switch ($this->_platform) {
                case 'vk':
                    if(!$this->getUserId()) {
                        return false;
                    } else {
                        $friendList = $this->_platformClass->api('friends.getAppUsers');
                        $friendList = $friendList['response'];
                        return $friendList;
                    }
                    break;
            }
        }
    }


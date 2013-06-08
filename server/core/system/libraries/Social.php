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
                    $this->_platformClass = new Vkapi($this->_globals->getParam('vk'));
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
            switch ($this->_platform) {
                case 'vk':
                    $this->_userId = $this->vkUserId();
                    break;
            }
            return $this->_userId;
        }

        /**
         * Профиль пользователя в соцсети
         * @return array|bool
         */
        public function getProfile() {
            switch ($this->_platform) {
                case 'facebook':
                    if(!$this->_userId) {
                        return false;
                    } else {
                        return $this->_platformClass->api('/me');
                    }
                    break;
            }
        }

        /**
         * Информация о запросе
         * @param int $requestId
         * @return array|bool
         */
        public function getRequestInfo($requestId) {
            switch ($this->_platform) {
                case 'facebook':
                    if(!$this->_userId) {
                        return false;
                    } else {
                        return $this->_platformClass->api($requestId);
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
                case 'facebook':
                    if(!$this->_userId) {
                        return false;
                    } else {
                        $_friendList = $this->_platformClass->api('/me/friends?fields=id,name,installed');
                        $_friendList = $_friendList['data'];
                        $friendList = array();
                        foreach($_friendList as $friend) {
                            $friendList[] = $friend;
                        }
                        return $friendList;
                    }
                    break;
            }
        }
        

		/**
         * @param $params array
		 * @return string
		 */
		public function getAuthUrl($params = array()) {
			switch ($this->_platform) {
				case 'facebook':
					if(!$this->getUserId()) {
						return $this->_platformClass->getLoginUrl($params);
					} else {
						return $this->_platformClass->getLogoutUrl(array('next' => 'http://bubble.battlekeys.com/server/index.php/index/logout'));
					}
					break;
			}
		}


        /**
         * @param $userId int
         * @param $requestId int
         * @return bool
         */
        public function deleteRequest($requestId, $userId) {
            switch ($this->_platform) {
                case 'facebook':
                    try {
                        return $this->_platformClass->api("/{$requestId}_{$userId}",'DELETE');
                    } catch(Exception $ex) {
                        return false;
                    }
                    break;
            }
        }
    }


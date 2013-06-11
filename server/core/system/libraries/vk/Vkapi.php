<?php
    class Vkapi {
        private $_apiSecret;
        private $_appId;
        private $_userId;
        private $_apiUrl;

        function Vkapi($params, $userId) {
            $this->_appId = $params['app_id'];
            $this->_userId = $userId;
            $this->_apiSecret = $params['api_secret'];
            if (!strstr($params['api_url'], 'http://')) {
                $params['api_url'] = 'http://'.$params['api_url'];
            }
            $this->_apiUrl = $params['api_url'];
        }

        public function api($method,$params = false, $userId = false) {
            if(!$userId) {
                $userId = $this->_userId;
            }
            if (!$params) {
                $params = array();
            }
            $params['api_id'] = $this->_appId;
            $params['uid'] = $userId;
            $params['v'] = '3.0';
            $params['method'] = $method;
            $params['timestamp'] = time();
            $params['format'] = 'json';
            $params['random'] = rand(0,10000);
            ksort($params);
            $sig = '';
            foreach($params as $k=>$v) {
                $sig .= $k.'='.$v;
            }
            $sig .= $this->_apiSecret;
            $params['sig'] = md5($sig);
            $query = $this->_apiUrl.'?'.$this->_params($params);
            $res = file_get_contents($query);
            return json_decode($res, true);
        }

        private function _params($params) {
            $pice = array();
            foreach($params as $k=>$v) {
                $pice[] = $k.'='.urlencode($v);
            }
            return implode('&',$pice);
        }
    }
?>

<?php
    class BattleModel extends RedisModel
    {
        /**
         * Создать самого себя
         *
         * @return BattleModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        public function sendMessage($userId, $recipient, $data) {
            $response = new Response();
            $data['user_id'] = $userId;

            $battleId = $this->_getBattleId($userId, $recipient);

            $battleData = $this->_getBattleData($battleId);

            if(!$this->_checkMessage($userId, $battleData, $data)) {
                return $response->setCode(Response::CODE_ERROR)->setError('Wrong message');
            }
            switch($data['type']) {
                case 'turn':
                    $battleData['phase'] = 'battle';
                    $battleData['turn']++;

                    if((time() - $battleData['update_time']) < 3) {
                        return $response->setCode(Response::CODE_ERROR)->setError('Too late');
                    }

                    $turnSteps = explode(',', $data['message']);
                    $stepArray = StepModel::getInstance()->getEntityListByEntityList($turnSteps)->getData();
                    if(count($stepArray) != count($turnSteps)) {
                        return $response->setCode(Response::CODE_ERROR)->setError('Not existing steps');
                    }
                    $userStepArray = UserStepModel::getInstance()->getUserStepPairsListByUserId($userId)->getData();

                    $sumStamina = 0;
                    $scores = 0;
                    foreach($stepArray as $step) {
                        $multiplier = in_array($step['id'], $battleData['steps']) ? 0.5 : 1;
                        $sumStamina += $step['stamina'];
                        if(!isset($userStepArray[$step['id']])) {
                            return $response->setCode(Response::CODE_ERROR)->setError('User don`t have such steps');
                        }
                        $scores += $step['mastery_points_'.$userStepArray[$step['id']]] * $multiplier;
                    }

                    $userData =  array('stamina' => -$sumStamina);

                    if(!($battleData['turn'] % 2) && $scores != $battleData['last_scores']) {
                        $userData['battles']    = 1;
                        $userData['wins']       = $scores > $battleData['last_scores'] ? 1 : 0;

                        $battleData['phase'] = 'finish';
                    }
                    $battleData['last_scores'] = $scores;
                    array_push($battleData['steps'], array_diff($turnSteps, $battleData['steps']));

                    $updateResult = UserModel::getInstance()->updateUserByUserId($userId, $userData);

                    if($updateResult->isError()) {
                        return $updateResult;
                    }
                    $userData = UserModel::getInstance()->getEntityByEntityId($userId)->getData();
                    $userData['scores'] = $scores;
                    $response->setData($userData);
                    break;
                case 'invite_send':
                    $battleData['messages']    = array();
                case 'invite_accept':
                    $battleData['phase']    = $data['type'];
                    $battleData['turn']     = 0;
                    $battleData['steps']    = array();
                    break;
                case 'timeout':
                    $battleData['messages']     = array();
                    $battleData['phase']        = 'finish';
                    $battleData['turn']         = 0;
                    $battleData['steps']        = array();
                    break;
            }

            $battleData['last_user']        = $userId;
            $battleData['update_time']      = time();
            array_push($battleData['messages'], $data);
            $this->_saveBattleData($battleId, $battleData);

            $this->_curlSend($recipient, $data);

            return $response;
        }

        private function _curlSend($recipient, $message) {
            $ch = curl_init();
            $data_string = json_encode($message);

            curl_setopt($ch, CURLOPT_URL, 'http://zluki.com/pub?cid='.$recipient);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $result = curl_exec($ch);

            curl_close($ch);

            return $result;
        }

        private function _checkMessage($userId, $battleData, $data) {
            switch($data['type']) {
                case 'timeout':
                    if($battleData['update_time'] > (time() - 60 * 2)) {
                        return false;
                    }
                    break;
                case 'turn':
                    if($battleData['phase'] != 'invite_accept' && $battleData['phase'] != 'battle') {
                        return false;
                    }

                    if($userId == $battleData['last_user']) {
                        return false;
                    }

                    $userData = UserModel::getInstance()->getEntityByEntityId($userId)->getData();
                    $levelData = LevelModel::getInstance()->getEntityByEntityId($userData['level'])->getData();
                    $steps = explode(',', $data['message']);

                    if(count($steps) > $levelData['max_moves']) {
                        return false;
                    }
                    break;
                case 'invite_send':
                    if($battleData['phase'] == 'battle' || $battleData['phase'] == 'invite_accept') {
                        return false;
                    }
                    break;
                case 'invite_accept':
                    if($userId == $battleData['last_user']) {
                        return false;
                    }

                    if($battleData['phase'] != 'invite_send') {
                        return false;
                    }
                    break;
                default:
                    return false;
            }

            return true;
        }

        private function _getBattleData($battleId) {
            $data = json_decode($this->getValueByKey($battleId), true);

            if(!$data) {
                $data = array(
                    'update_time'   => time(),
                    'last_user'     => 0,
                    'last_scores'   => 0,
                    'turn'          => 0,
                    'phase'         => 'start',
                    'steps'         => array(),
                    'messages'      => array()
                );
            }

            return $data;
        }

        private function _saveBattleData($battleId, $battleData) {
            return $this->setValueByKey($battleId, $battleData);
        }

        private function _getBattleId($firstUser, $secondUser) {
            $usersArray = array($firstUser, $secondUser);
            sort($usersArray);
            return implode('', $usersArray);
        }
    }

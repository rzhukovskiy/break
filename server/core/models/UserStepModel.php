<?php
    /**
     * работа с user_move таблицей. движения пользователей
     */
    class UserStepModel extends BaseModel {
        protected $_table = 'user_move';

        /**
         * Создать самого себя
         *
         * @return UserStepModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список движений у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserStepListByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    ' . $this->_table . '
                WHERE
                    user_id = :user_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Получить конкретный предмет у конкретного пользователя
         * @param int $userId
         * @param string $stepId
         * @return Response
         */
        public function getUserStepByUserIdAndStepId($userId, $stepId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    *
                FROM
                    ' . $this->_table . '
                WHERE
                    user_id = :user_id AND
                    step_id = :step_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':step_id' => $stepId,
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Потренить движение
         * @param int $userId
         * @param string $stepId
         * @return Response
         */
        public function trainUserStep($userId, $stepId) {
            /** @var $dataDb PDO */
            $response = new Response();

            $step = StepModel::getInstance()->getEntityByEntityId($stepId);
            if($step->isError()) {
                return $step;
            }
            $step = $step->getData();

            $userStep = $this->getUserStepByUserIdAndStepId($userId, $stepId);
            if($userStep->isError()) {
                return $userStep;
            }
            $userStep = $userStep->getData();

            if(!$this->_checkTrainConditions($userId, $step, $userStep)) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong conditions');
            }

            $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                'energy' => -1 * $step['energy_' . (isset($userStep['level']) ? $userStep['level'] + 1 : 0)],
                'coins'  => -1 * $step['coins_' . (isset($userStep['level']) ? $userStep['level'] + 1 : 0)]
            ));
            if($awardResult->isError()) {
                return $awardResult;
            }

            $raiseResult = $this->raiseUserStepLevel($userId, $stepId);
            if($raiseResult->isError()) {
                return $raiseResult;
            }
            return $awardResult;
        }

        /**
         * Добавить уровень указанному движению
         * @param int $userId
         * @param string $stepId
         * @return Response
         */
        public function raiseUserStepLevel($userId, $stepId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'INSERT INTO
                    ' . $this->_table . '
                    (user_id, step_id, level, modify_date)
                VALUES
                    (:user_id, :step_id, 1, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    level = level + 1,
                    modify_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':step_id'      => $stepId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * @param $userId
         * @param $step
         * @param $userStep
         * @return bool
         */
        private function _checkTrainConditions($userId, $step, $userStep)
        {
            if($userStep) {
                return true;
            }
            switch($step['condition_type']) {
                case 'step':
                    list($stepId, $stepLevel) = explode(':', $step['condition_value']);
                    $userStep = $this->getUserStepByUserIdAndStepId($userId, $stepId)->getData();
                    if($userStep['level'] >= $stepLevel) {
                        return true;
                    }
                    break;
                default:
                    $user = UserModel::getInstance()->getEntityByEntityId($userId);
                    if($user->isError()) {
                        return false;
                    }
                    $user = $user->getData();
                    return $user[$step['condition_type']] == $step['condition_value'];
            }

            return false;
        }
    }

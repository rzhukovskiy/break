<?php
    /**
     * работа с user_move таблицей. движения пользователей
     */
    class UserStepModel extends BaseModel {
        protected $_table = 'user_step';

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
         * Получить конкретное движение у конкретного пользователя
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
         * @param int $energySpent
         * @return Response
         */
        public function trainUserStep($userId, $stepId, $energySpent) {
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
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong conditions');
            }

            $learningStepLevel = isset($userStep['level']) ? $userStep['level'] + 1 : 1;
            $coinsCost = isset($step['coins_' . $learningStepLevel]) ? -1 * $step['coins_' . $learningStepLevel] : 0;
            $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                'energy'        => -1 * $energySpent,
                'coins'         => $coinsCost,
                'energy_spent'  => $energySpent
            ));
            if($awardResult->isError()) {
                return $awardResult;
            }

            $newEnergy = isset($userStep['energy_spent']) ? $userStep['energy_spent'] + $energySpent : $energySpent;
            $neededEnergy = $step['energy_' . $learningStepLevel] - (isset($step['energy_' . ($learningStepLevel - 1)]) ? $step['energy_' . ($learningStepLevel - 1)] : 0);
            if($newEnergy >= $neededEnergy) {
                $raiseResult = $this->raiseUserStepLevel($userId, $stepId, $newEnergy - $neededEnergy);
            } else {
                $raiseResult = $this->raiseUserStepEnergy($userId, $stepId, $energySpent);
            }

            if($raiseResult->isError()) {
                return $raiseResult;
            }
            return $awardResult;
        }

        /**
         * Добавить уровень указанному движению
         * @param int $userId
         * @param string $stepId
         * @param int $energySpent
         * @return Response
         */
        public function raiseUserStepLevel($userId, $stepId, $energySpent) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'INSERT INTO
                    ' . $this->_table . '
                    (user_id, step_id, energy_spent, level, create_date)
                VALUES
                    (:user_id, :step_id, :energy_spent, 1, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    level = level + 1,
                    energy_spent = :energy_spent';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':step_id'      => $stepId,
                ':energy_spent' => $energySpent
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Добавить 'ythub. указанному движению
         * @param int $userId
         * @param string $stepId
         * @param int $energySpent
         * @return Response
         */
        public function raiseUserStepEnergy($userId, $stepId, $energySpent) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, step_id, energy_spent, level, create_date)
                VALUES
                    (:user_id, :step_id, :energy_spent, 0, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    energy_spent = energy_spent + :energy_spent';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':step_id'      => $stepId,
                ':energy_spent' => $energySpent
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
         * @param $conditionStep
         * @return bool
         */
        private function _checkTrainConditions($userId, $step, $conditionStep)
        {
            if($conditionStep) {
                return true;
            }
            switch($step['condition_type']) {
                case 'step':
                    foreach(explode(',', $step['condition_value']) as $stepCondition) {
                        list($stepId, $stepLevel) = explode(':', $stepCondition);
                        $conditionStep = $this->getUserStepByUserIdAndStepId($userId, $stepId)->getData();
                        if(!$conditionStep || $conditionStep['level'] < $stepLevel) {
                            return false;
                        }
                    }
                    return true;
                    break;
                case '':
                    return true;
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

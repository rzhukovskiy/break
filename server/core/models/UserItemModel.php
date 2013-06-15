<?php
    /**
     * работа с user_item таблицей. предметы, принадлежащие юзерам
     */
    class UserItemModel extends BaseModel {
        protected $_table = 'user_item';

        /**
         * Создать самого себя
         *
         * @return UserItemModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список предметов у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserItemListByUserId($userId) {
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
         * @param string $itemId
         * @return Response
         */
        public function getUserItemByUserIdAndItemId($userId, $itemId) {
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
                ':step_id' => $itemId,
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
         * Купить предмет
         * @param int $userId
         * @param string $itemId
         * @return Response
         */
        public function buyUserItem($userId, $itemId) {
            /** @var $dataDb PDO */
            $response = new Response();

            $item = ItemModel::getInstance()->getEntityByEntityId($itemId);
            if($item->isError()) {
                return $item;
            }
            $item = $item->getData();

            if(!$this->_checkItemConditions($userId, $item)) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong conditions');
            }

            $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                'coins'  => -1 * $item['coins']
            ));
            if($awardResult->isError()) {
                return $awardResult;
            }

            $raiseResult = $this->addUserItem($userId, $itemId);
            if($raiseResult->isError()) {
                return $raiseResult;
            }
            return $awardResult;
        }

        /**
         * применяем предмет
         * @param int $userId
         * @param array $item
         * @return Response
         */
        public function applyUserItem($userId, $item) {
            $response = new Response();

            if($item['type']) {
                $response = UserModel::getInstance()->updateUserByUserId($userId, array($item['type'] => $item['power']));
            }

            return $response;
        }

        /**
         * Добавить предмет
         * @param int $userId
         * @param string $itemId
         * @return Response
         */
        public function addUserItem($userId, $itemId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, item_id, amount, create_date)
                VALUES
                    (:user_id, :item_id, 1, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    amount = amount + 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':item_id'      => $itemId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * @param $userId
         * @param $item
         * @return bool
         */
        private function _checkItemConditions($userId, $item)
        {
            switch($item['condition_type']) {
                case 'step':
                    list($stepId, $stepLevel) = explode(':', $item['condition_value']);
                    $conditionStep = UserStepModel::getInstance()->getUserStepByUserIdAndStepId($userId, $stepId);
                    if($conditionStep->isError()) {
                        return $conditionStep;
                    }
                    $conditionStep = $conditionStep->getData();
                    if(!$conditionStep) {
                        return false;
                    }
                    return $conditionStep['level'] >= $stepLevel;
                    break;
                default:
                    $user = UserModel::getInstance()->getEntityByEntityId($userId);
                    if($user->isError()) {
                        return false;
                    }
                    $user = $user->getData();
                    return $user[$item['condition_type']] == $item['condition_value'];
            }

            return false;
        }
    }

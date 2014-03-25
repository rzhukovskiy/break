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
                    item_id = :item_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':item_id' => $itemId,
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
         * @param string $color
         * @return Response
         */
        public function buyUserItem($userId, $itemId, $color) {
            /** @var $dataDb PDO */
            $response = new Response();

            $item = ItemModel::getInstance()->getEntityByEntityId($itemId);
            if($item->IsNotOk()) {
                return $item;
            }
            $item = $item->getData();

            if(!$this->_checkItemConditions($userId, $itemId)) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong conditions');
            }

            if(($item['coins'] > 0) || ($item['bucks'] > 0)) {
                $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                    'coins'  => -1 * $item['coins'],
                    'bucks'  => -1 * $item['bucks']
                ));
                if($awardResult->IsNotOk()) {
                    return $awardResult;
                }
            }

            $addResult = $this->addUserItem($userId, $itemId, $color);
            if($addResult->IsNotOk()) {
                return $addResult;
            }
            $response->setData(array_merge(UserModel::getInstance()->getEntityByEntityId($userId)->getData(), $addResult->getData()));
            return $response;
        }

        /**
         * Продать предмет
         * @param int $userId
         * @param string $userItemId
         * @return Response
         */
        public function sellUserItem($userId, $userItemId) {
            /** @var $dataDb PDO */
            $response = new Response();

            $userItem = UserItemModel::getInstance()->getEntityByEntityId($userItemId);
            if($userItem->IsNotOk()) {
                return $userItem;
            }
            $userItem = $userItem->getData();

            $item = ItemModel::getInstance()->getEntityByEntityId($userItem['item_id']);
            if($item->IsNotOk()) {
                return $item;
            }
            $item = $item->getData();

            if(($item['coins'] > 0) || ($item['bucks'] > 0)) {
                $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                    'coins'  => $item['coins'] / 2,
                    'bucks'  => $item['bucks'] / 2
                ));
                if($awardResult->IsNotOk()) {
                    return $awardResult;
                }
            }

            $addResult = $this->removeEntityById($userId, $userItemId);
            if($addResult->IsNotOk()) {
                return $addResult;
            }
            $response->setData(UserModel::getInstance()->getEntityByEntityId($userId)->getData());
            return $response;
        }

        /**
         * применяем предмет
         * @param int $userId
         * @param string $itemId
         * @return Response
         */
        private function _applyUserItem($userId, $itemId) {
            $response = new Response();

            $item = ItemModel::getInstance()->getEntityByEntityId($itemId);
            if($item->IsNotOk()) {
                return $item;
            }
            $item = $item->getData();

            if($item['bonus_type'] and $item['bonus_type'] != 'client' && $item['bonus_value']) {
                $response = UserModel::getInstance()->updateUserByUserId($userId, array($item['bonus_type'] => $item['bonus_value']));
            }

            return $response;
        }

        /**
         * Добавить предмет
         * @param int $userId
         * @param string $itemId
         * @param string $color
         * @return Response
         */
        public function addUserItem($userId, $itemId, $color = 'no_color') {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, item_id, color, create_date)
                VALUES
                    (:user_id, :item_id, :color, CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':item_id'      => $itemId,
                ':color'        => $color
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response->setData(array('user_item_id' => $dataDb->lastInsertId()));
        }

        /**
         * @param $userId
         * @param $item
         * @return bool
         */
        private function _checkItemConditions($userId, $item)
        {
            $item = ItemModel::getInstance()->getEntityByEntityId($item);
            if($item->IsNotOk()) {
                return false;
            }

            $item = $item->getData();
            switch($item['condition_type']) {
                case 'step':
                    list($stepId, $stepLevel) = explode(':', $item['condition_value']);
                    $conditionStep = UserStepModel::getInstance()->getUserStepByUserIdAndStepId($userId, $stepId);
                    if($conditionStep->IsNotOk()) {
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
                    if($user->IsNotOk()) {
                        return false;
                    }
                    $user = $user->getData();
                    return $user[$item['condition_type']] >= $item['condition_value'];
            }

            return false;
        }
    }

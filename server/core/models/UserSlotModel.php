<?php
    /**
     * работа с user_item таблицей. предметы, принадлежащие юзерам
     */
    class UserSlotModel extends BaseModel {
        protected $_table = 'user_slot';

        /**
         * Создать самого себя
         *
         * @return UserSlotModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список предметов у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserSlotListByUserId($userId) {
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
         * @param string $slotId
         * @return Response
         */
        public function getUserSlotByUserIdAndSlotId($userId, $slotId) {
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
                    slot_id = :slot_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':step_id' => $slotId,
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
         * @param string $slotId
         * @param string $itemId
         * @return Response
         */
        public function equipUserSlot($userId, $slotId, $itemId) {
            /** @var $dataDb PDO */
            $response = new Response();

            if(!$this->_checkItem($userId, $itemId)) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('User don`t have this item');
            }

            $addResult = $this->addUserItemToSlot($userId, $slotId, $itemId);
            if($addResult->isError()) {
                return $addResult;
            }
            return $addResult;
        }

        /**
         * Добавить предмет
         * @param int $userId
         * @param string $slotId
         * @param string $itemId
         * @return Response
         */
        public function addUserItemToSlot($userId, $slotId, $itemId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, slot_id, item_id)
                VALUES
                    (:user_id, :slot_id, :item_id)
                ON DUPLICATE KEY UPDATE
                    item_id = :item_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':slot_id'      => $slotId,
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
         * @param $itemId
         * @return bool
         */
        private function _checkItem($userId, $itemId)
        {
            $result = UserItemModel::getInstance()->getUserItemByUserIdAndItemId($userId, $itemId);
            return !$result->isError() && $result->getData();
        }
    }

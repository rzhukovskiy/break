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
         * @param string $bonusType
         * @return Response
         */
        public function getUserSlotByUserIdAndBonusType($userId, $bonusType) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    *
                FROM
                    ' . $this->_table . '
                WHERE
                    user_id     = :user_id AND
                    bonus_type  = :bonus_type';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':bonus_type'   => $bonusType,
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $response->setData($query->fetchAll(PDO::FETCH_ASSOC));

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
                ':slot_id' => $slotId,
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $response->setData($query->fetch(PDO::FETCH_ASSOC));

            return $response;
        }

        /**
         * Экипировать предмет
         * @param int $userId
         * @param string $slotId
         * @param string $userItemId
         * @return Response
         */
        public function equipUserSlot($userId, $slotId, $userItemId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $userItem = UserItemModel::getInstance()->getEntityByEntityId($userItemId);
            if($userItem->isError()) {
                return $userItem;
            }
            $userItem = $userItem->getData();

            $itemData = ItemModel::getInstance()->getEntityByEntityId($userItem['item_id']);
            if($itemData->isError()) {
                return $itemData;
            }
            $itemData = $itemData->getData();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, slot_id, user_item_id, bonus_type, bonus_value)
                VALUES
                    (:user_id, :slot_id, :user_item_id, :bonus_type, :bonus_value)
                ON DUPLICATE KEY UPDATE
                    user_item_id = :user_item_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':slot_id'      => $slotId,
                ':user_item_id' => $userItemId,
                ':bonus_type'   => $itemData['bonus_type'],
                ':bonus_value'  => $itemData['bonus_value']
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

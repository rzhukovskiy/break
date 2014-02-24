<?php
    /**
     * работа c user_consumables таблицей. иcпользуемые предметы, принадлежащие юзерам
     */
    class UserConsumablesModel extends BaseModel {
        protected $_table = 'user_consumables';

        /**
         * cоздать cамого cебя
         *
         * @return UserConsumablesModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * cпиcок предметов у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserConsumablesListByUserId($userId) {
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
         * @param string $consumablesId
         * @return Response
         */
        public function getUserConsumablesByUserIdAndConsumablesId($userId, $consumablesId) {
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
                    consumables_id = :consumables_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':consumables_id' => $consumablesId,
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
         * @param string $consumablesId
         * @param int $count
         * @return Response
         */
        public function buyUserConsumables($userId, $consumablesId, $count = 1) {
            /** @var $dataDb PDO */
            $response = new Response();

            $consumables = ConsumablesModel::getInstance()->getEntityByEntityId($consumablesId);
            if($consumables->isError()) {
                return $consumables;
            }
            $consumables = $consumables->getData();

            if(($consumables['coins'] > 0) || ($consumables['bucks'] > 0)) {
                $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array(
                    'coins'  => -1 * $consumables['coins'] * $count,
                    'bucks'  => -1 * $consumables['bucks'] * $count
                ));
                if($awardResult->isError()) {
                    return $awardResult;
                }
            }

            $addResult = $this->addUserConsumables($userId, $consumablesId, $count);
            if($addResult->isError()) {
                return $addResult;
            }
            $response->setData(array_merge(UserModel::getInstance()->getEntityByEntityId($userId)->getData(), $addResult->getData()));
            return $response;
        }

        /**
         * применяем предмет
         * @param int $userId
         * @param array $consumablesId
         * @return Response
         */
        public function applyUserConsumables($userId, $consumablesId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $consumables = ConsumablesModel::getInstance()->getEntityByEntityId($consumablesId);
            if($consumables->isError()) {
                return $consumables;
            }
            $consumables = $consumables->getData();

            if($consumables['bonus_type'] && $consumables['bonus_type'] != 'client' && $consumables['bonus_value']) {
                $awardResult = UserModel::getInstance()->updateUserByUserId($userId, array($consumables['bonus_type'] => $consumables['bonus_value']));
                if($awardResult->isError()) {
                    return $awardResult;
                }
            }

            $sql =
                'UPDATE '
                . $this->_table .
                ' SET
                  count = count - 1,
                  apply_date = CURRENT_TIMESTAMP
                WHERE
                  user_id        = :user_id AND
                  consumables_id = :consumables_id AND
                  count          >= 1';

            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':consumables_id'   => $consumablesId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $response->setData($this->getUserConsumablesListByUserId($userId)->getData());

            return $response;
        }

        /**
         * Добавить предмет
         * @param int $userId
         * @param string $consumablesId
         * @param int $count
         * @return Response
         */
        public function addUserConsumables($userId, $consumablesId, $count = 1) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, consumables_id, count, create_date)
                VALUES
                    (:user_id, :consumables_id, :count, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    count = count + :count';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':consumables_id'   => $consumablesId,
                ':count'            => $count
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

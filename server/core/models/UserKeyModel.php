<?php
    /**
     * работа с user_item таблицей. предметы коллекций, принадлежащие юзерам
     */
    class UserKeyModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return UserKeyModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список ключей у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserKeyListByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    user_key
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
         * Список определенных ключей у определенного пользователя
         * @param int $userId
         * @param array $userKeyList
         * @return Response
         */
        public function getUserKeyListByUserKeyList($userId, $userKeyList) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    *
                FROM
                    user_key
                WHERE
                    user_id = :user_id AND
                    location_id IN ("' . implode('","', $userKeyList) . '") AND
                    amount > 0';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId
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
         * Получить конкретный ключ у конкретного пользователя
         * @param int $userId
         * @param string $locationId
         * @return Response
         */
        public function getUserKeyByUserIdAndLocationId($userId, $locationId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    user_key
                WHERE
                    user_id = :user_id AND
                    item_id = :item_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':location_id' => $locationId,
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
         * Добавить пользователю указанный ключ
         * @param int $userId
         * @param string $locationId
         * @param int $amount
         * @return Response
         */
        public function addUserKey($userId, $locationId, $amount = 1) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'INSERT INTO
                    user_key
                    (user_id, location_id, amount, modify_date)
                VALUES
                    (:user_id, :location_id, :amount, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    amount = amount + :amount,
                    modify_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':location_id'  => $locationId,
                ':amount'       => $amount
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Забрать у пользователя список предметов
         * @param int $userId
         * @param string $locationId
         * @param int $amount
         * @return Response
         */
        public function takeUserKey($userId, $locationId, $amount = 1) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                    user_key
                SET
                    amount = amount - :amount
                WHERE
                    user_id = :user_id AND
                    location_id = :location_id AND
                    amount - :amount >= 0';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':location_id'      => $locationId,
                ':amount'           => $amount
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            if($query->rowCount() < 1) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Not enough keys');
            }

            return $response;
        }
    }

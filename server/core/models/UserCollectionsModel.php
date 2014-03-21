<?php
    /**
     * работа с user_collections таблицей. предметы коллекций, принадлежащие юзерам
     */
    class UserCollectionsModel extends BaseModel {
        protected $_table = 'user_collections';

        /**
         * Создать самого себя
         *
         * @return UserCollectionsModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список предметов у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserCollectionsListByUserId($userId) {
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
         * @param string $collectionsId
         * @return Response
         */
        public function getUserCollectionsByUserIdAndCollectionsId($userId, $collectionsId) {
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
                    collections_id = :collections_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':collections_id' => $collectionsId,
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
         * применяем предмет
         * @param int $userId
         * @return Response
         */
        public function giveUserCollections($userId) {
            $response = new Response();

            $collectionsData = CollectionsModel::getInstance()->getCollectionsIdByChance(rand(0, 100));;
            if($collectionsData->isError()) {
                return $collectionsData;
            }
            $collectionsData = $collectionsData->getData();
            if(!isset($collectionsData['id'])){
                $response->setData(array('collections_id' => 0));
                return $response;
            }

            $collectionsId = $collectionsData['id'];

            $userCollections = $this->getUserCollectionsByUserIdAndCollectionsId($userId, $collectionsId);
            if($userCollections->isError()) {
                return $userCollections;
            }
            $userCollections = $userCollections->getData();

            if(isset($userCollections['amount']) && $userCollections['amount'] > 3) {
                $amount = 0;
                $response = UserModel::getInstance()->updateUserByUserId($userId, array('chips' => 1));
                if($response->isError()) {
                    return $response;
                }
            } else {
                $amount = isset($userCollections['amount']) ? $userCollections['amount'] + 1 : 1;
            }

            $response = $this->addUserCollections($userId, $collectionsId, $amount);
            if($response->isError()) {
                return $response;
            }

            $response->setData(array('collections_id' => $collectionsId));
            return $response;
        }

        /**
         * Добавить предмет
         * @param int $userId
         * @param string $collectionsId
         * @param int $amount
         * @return Response
         */
        public function addUserCollections($userId, $collectionsId, $amount = 1) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, collections_id, create_date, amount)
                VALUES
                    (:user_id, :collections_id, CURRENT_TIMESTAMP, :amount)
                ON DUPLICATE KEY UPDATE
                    amount = :amount';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':collections_id'   => $collectionsId,
                ':amount'           => $amount
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

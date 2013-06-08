<?php
    /**
     * работа с user_chest таблицей. открытые юзером сундуки
     */
    class UserChestModel extends BaseModel {
        private $_table = 'user_chest';
        /**
         * Создать самого себя
         *
         * @return UserChestModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список сундуков открытых пользователем
         * @param int $userId
         * @return Response
         */
        public function getUserChestListByUserId($userId) {
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
         * Получить конкретный сундук у конкретного пользователя
         * @param int $userId
         * @param string $chestId
         * @return Response
         */
        public function getUserChestByUserIdAndChestId($userId, $chestId) {
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
                    chest_id = :chest_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':chest_id' => $chestId,
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
         * применяем сундук
         * @param int $userId
         * @param string $chestId
         * @return Response
         */
        public function applyChest($userId, $chestId) {
            $response = new Response();

            $chest = ChestModel::getInstance()->getChestByChestId($chestId);
            if($chest->isError()) {
                return $chest;
            }
            $chest = $chest->getData();

            if($chest['type'] != 'client') {
                $response = UserModel::getInstance()->updateUserByUserId($userId, array($chest['type'] => $chest['power']));
            }

            return $response;
        }

        /**
         * Открываем сундук за ключи
         * @param int $userId
         * @param string $chestId
         * @return Response
         */
        public function openChestForKeys($userId, $chestId) {
            $chest = ChestModel::getInstance()->getChestByChestId($chestId);
            if($chest->isError()) {
                return $chest;
            }
            $chest = $chest->getData();

            if($chest['keys']) {
                $keyList = explode(';', $chest['keys']);
                foreach($keyList as $keyData) {
                    list($keyId, $amount) = explode(' ', $keyData);
                    $response = UserKeyModel::getInstance()->takeUserKey($userId, $keyId, $amount);
                    if($response->isError()) {
                        return $response;
                    }
                }
            }

            $response = $this->addUserChest($userId, $chestId);
            if($response->isError()) {
                return $response;
            }

            return $this->applyChest($userId, $chestId);
        }

        /**
         * Открываем сундук за кристаллы
         * @param int $userId
         * @param string $chestId
         * @return Response
         */
        public function openChestForDiamonds($userId, $chestId) {
            $settings = $this->getSettingList();
            $chest = ChestModel::getInstance()->getChestByChestId($chestId);
            if($chest->isError()) {
                return $chest;
            }
            $chest = $chest->getData();

            $response = UserModel::getInstance()->updateUserByUserId($userId, array(
                'diamonds'  => -1 * $chest['fb_credits'] * $settings['diamonds_rate'],
            ));
            if($response->isError()) {
                return $response;
            }

            $response = $this->addUserChest($userId, $chestId);
            if($response->isError()) {
                return $response;
            }

            return $this->applyChest($userId, $chestId);
        }

        /**
         * Открываем сундук за кредиты
         * @param int $userId
         * @param string $chestId
         * @param int $amount
         * @return Response
         */
        public function openChestForCredits($userId, $chestId, $amount) {
            $response = new Response();

            $chest = ChestModel::getInstance()->getChestByChestId($chestId);
            if($chest->isError()) {
                return $chest;
            }
            $chest = $chest->getData();

            if($chest['fb_credits'] > $amount) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong credit amount');
            }

            $response = $this->addUserChest($userId, $chestId);
            if($response->isError()) {
                return $response;
            }

            return $this->applyChest($userId, $chestId);
        }

        /**
         * Добавить пользователю указанный сундук
         * @param int $userId
         * @param string $chestId
         * @return Response
         */
        public function addUserChest($userId, $chestId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, chest_id)
                VALUES
                    (:user_id, :chest_id)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':chest_id'    => $chestId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            if($query->rowCount() < 1) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Chest is already opened');
            }

            return $response;
        }
    }

<?php
    /**
     * работа с user_amulet таблицей. амулеты, принадлежащие юзерам
     */
    class UserAmuletModel extends BaseModel {
        private $_table = 'user_amulet';
        /**
         * Создать самого себя
         *
         * @return UserAmuletModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список амулетов у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserAmuletListByUserId($userId) {
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
         * Список амулетов у определенного пользователя с указанным статусом
         * @param int $userId
         * @param string $status
         * @return Response
         */
        public function getUserAmuletListByUserIdAndStatus($userId, $status) {
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
                    status = :status';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':status' => $status
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
         * Получить конкретный амулет у конкретного пользователя
         * @param int $userId
         * @param string $amuletId
         * @return Response
         */
        public function getUserAmuletByUserIdAndAmuletId($userId, $amuletId) {
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
                    amulet_id = :amulet_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':scroll_id' => $amuletId,
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
         * Купить пользователю указанный амулет
         * @param int $userId
         * @param string $amuletId
         * @return Response
         */
        public function buyUserAmulet($userId, $amuletId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $settings = $this->getSettingList();
            $response = new Response();

            $amulet = AmuletModel::getInstance()->getAmuletByAmuletId($amuletId);
            if($amulet->isError()) {
                return $amulet;
            }
            $amulet = $amulet->getData();

            $mission = UserMissionModel::getInstance()->getUserMissionByUserIdAndMissionId($userId, $amulet['mission_id']);
            if($mission->isError()) {
                return $mission;
            }
            if(!$mission->getData()) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Not opened yet');
            }

            $response = UserModel::getInstance()->updateUserByUserId($userId, array(
                'diamonds'  => -1 * $amulet['fb_credits'] * $settings['diamonds_rate'],
            ));
            if($response->isError()) {
                return $response;
            }

            return $this->addUserAmulet($userId, $amuletId);
        }

        /**
         * Купить пользователю указанный амулет
         * @param int $userId
         * @param string $amuletId
         * @param int $amount
         * @return Response
         */
        public function buyUserAmuletForCredits($userId, $amuletId, $amount) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $amulet = AmuletModel::getInstance()->getAmuletByAmuletId($amuletId);
            if($amulet->isError()) {
                return $amulet;
            }
            $amulet = $amulet->getData();

            $mission = UserMissionModel::getInstance()->getUserMissionByUserIdAndMissionId($userId, $amulet['mission_id']);
            if($mission->isError()) {
                return $mission;
            }
            if(!$mission->getData()) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Not opened yet');
            }

            if($amulet['fb_credits'] > $amount) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong credit amount');
            }

            return $this->addUserAmulet($userId, $amuletId);
        }

        /**
         * Добавить пользователю указанный амулет
         * @param int $userId
         * @param string $amuletId
         * @param string $status
         * @return Response
         */
        public function addUserAmulet($userId, $amuletId, $status = 'active') {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, amulet_id, status, modify_date)
                VALUES
                    (:user_id, :amulet_id, :status, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    status = "active",
                    modify_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':amulet_id'    => $amuletId,
                ':status'       => $status
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $this->applyAmulet($userId, $amuletId);
        }

        /**
         * Добавить пользователю указанный амулет
         * @param int $userId
         * @param string $amuletId
         * @return Response
         */
        public function addTestUserAmulet($userId, $amuletId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $amulet = AmuletModel::getInstance()->getAmuletByAmuletId($amuletId);
            if($amulet->isError()) {
                return $amulet;
            }
            $amulet = $amulet->getData();

            $mission = UserMissionModel::getInstance()->getUserMissionByUserIdAndMissionId($userId, $amulet['mission_id']);
            if($mission->isError()) {
                return $mission;
            }
            if(!$mission->getData()) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Not opened yet');
            }

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, amulet_id, status, modify_date)
                VALUES
                    (:user_id, :amulet_id, "new", CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':amulet_id'    => $amuletId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $this->applyAmulet($userId, $amuletId);
        }

        /**
         * Использует текущие тестовые амулеты пользователя
         * @param int $userId
         * @return Response
         */
        public function useAmulets($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $response = $this->removeAmulets($userId);

            if($response->isError()) {
                return $response;
            }

            $sql =
                'UPDATE
                    ' . $this->_table . '
                SET
                    status = "tested"
                WHERE
                    user_id = :user_id AND
                    status = "new"';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * применяем амулет
         * @param int $userId
         * @param string $amuletId
         * @return Response
         */
        public function applyAmulet($userId, $amuletId) {
            $response = new Response();

            $amulet = AmuletModel::getInstance()->getAmuletByAmuletId($amuletId);
            if($amulet->isError()) {
                return $amulet;
            }
            $amulet = $amulet->getData();

            if($amulet['type'] != 'client') {
                $response = UserModel::getInstance()->updateUserByUserId($userId, array($amulet['type'] => $amulet['power']));
            }

            return $response;
        }

        /**
         * отменяем амулет
         * @param int $userId
         * @param string $amuletId
         * @return Response
         */
        public function removeAmulet($userId, $amuletId) {
            $response = new Response();

            $amulet = AmuletModel::getInstance()->getAmuletByAmuletId($amuletId);
            if($amulet->isError()) {
                return $amulet;
            }
            $amulet = $amulet->getData();

            if($amulet['type'] != 'client') {
                $response = UserModel::getInstance()->updateUserByUserId($userId, array($amulet['type'] => -1 * $amulet['power']));
            }

            return $response;
        }

        /**
         * удаляем эффекты амулетов
         * @param int $userId
         * @return Response
         */
        public function removeAmulets($userId) {
            $response = new Response();

            $amuletList = $this->getUserAmuletListByUserIdAndStatus($userId, 'new');
            if($amuletList->isError()) {
                return $amuletList;
            }
            $amuletList = $amuletList->getData();

            foreach($amuletList as $userAmulet) {
                $response = $this->removeAmulet($userId, $userAmulet['amulet_id']);

                if($response->isError()) {
                    return $response;
                }
            }

            return $response;
        }
    }

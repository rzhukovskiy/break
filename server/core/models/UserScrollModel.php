<?php
    /**
     * работа с user_item таблицей. предметы коллекций, принадлежащие юзерам
     */
    class UserScrollModel extends BaseModel {
        private $_table = 'user_scroll';
        /**
         * Создать самого себя
         *
         * @return UserScrollModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список свитков у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserScrollListByUserId($userId) {
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
         * Список определенных свитков у определенного пользователя
         * @param int $userId
         * @param array $userScrollList
         * @return Response
         */
        public function getUserScrollListByUserScrollList($userId, $userScrollList) {
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
                    scroll_id IN ("' . implode('","', $userScrollList) . '") AND
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
         * Получить конкретный свиток у конкретного пользователя
         * @param int $userId
         * @param string $scrollId
         * @return Response
         */
        public function getUserScrollByUserIdAndScrollId($userId, $scrollId) {
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
                    scroll_id = :scroll_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':scroll_id' => $scrollId,
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
         * Купить пользователю указанный свиток
         * @param int $userId
         * @param string $scrollId
         * @return Response
         */
        public function buyUserScroll($userId, $scrollId, $type = 'coins') {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $settings = $this->getSettingList();
            $response = new Response();

            $scroll = ScrollModel::getInstance()->getScrollByScrollId($scrollId);
            if($scroll->isError()) {
                return $scroll;
            }
            $scroll = $scroll->getData();

            $stars = UserMissionModel::getInstance()->getUserMissionStars($userId);
            if($stars->isError()) {
                return $stars;
            }
            $stars = $stars->getData();

            if($stars['total_stars'] < $scroll['stars']) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Not enough stars');
                return $response;
            }

            $response = UserModel::getInstance()->updateUserByUserId($userId, array(
                $type     => -1 * $scroll['fb_credits'] * $settings[$type . '_rate']
            ));
            if($response->isError()) {
                return $response;
            }

            return $this->addUserScroll($userId, $scrollId);
        }

        /**
         * Добавить пользователю указанный свиток
         * @param int $userId
         * @param string $scrollId
         * @return Response
         */
        public function addUserScroll($userId, $scrollId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $scroll = ScrollModel::getInstance()->getScrollByScrollId($scrollId);
            if($scroll->isError()) {
                return $scroll;
            }
            $scroll = $scroll->getData();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, scroll_id, amount, modify_date)
                VALUES
                    (:user_id, :scroll_id, :amount, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    amount = amount + :amount,
                    modify_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':scroll_id'    => $scrollId,
                ':amount'       => 3
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Забрать у пользователя список свитков
         * @param int $userId
         * @param array $userScrollList
         * @return Response
         */
        public function takeUserScrollList($userId, $userScrollList) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                    ' . $this->_table . '
                SET
                    amount = amount - 1
                WHERE
                    user_id = :user_id AND
                    scroll_id IN ("' . implode('","', $userScrollList) . '") AND
                    amount > 0';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            if($query->rowCount() < 1) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Scrolls expired');
            }

            return $response;
        }

        /**
         * Использует текущие свитки пользователя
         * @param int $userId
         * @return Response
         */
        public function useScrolls($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                    ' . $this->_table . ',
                    scroll
                SET
                    ' . $this->_table . '.amount = ' . $this->_table . '.amount - 1
                WHERE
                    ' . $this->_table . '.user_id = :user_id AND
                    scroll.auto = 1 AND
                    scroll.id = scroll_id AND
                    ' . $this->_table . '.amount > 0';
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
    }

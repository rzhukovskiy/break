<?php
    /**
     * работа с user_tutorial таблицей. прогресс в туториале
     */
    class UserAwardModel extends BaseModel {
        protected $_table = 'user_award';

        /**
         * Создать самого себя
         *
         * @return UserAwardModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список шагов тутора у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserAwardListByUserId($userId) {
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
         * Получить конкретный тутор у конкретного пользователя
         * @param int $userId
         * @param string $awardId
         * @return Response
         */
        public function giveAward($userId, $awardId) {
            $response = $this->saveAward($userId, $awardId);
            if($response->IsNotOk()) {
                return $response;
            }

            $response = UserModel::getInstance()->giveAward($userId, $awardId);
            return $response;
        }

        /**
         * Получить конкретный тутор у конкретного пользователя
         * @param int $userId
         * @param string $awardId
         * @return Response
         */
        public function getUserAwardByUserIdAndAwardId($userId, $awardId) {
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
                    award_id = :award_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':award_id' => $awardId,
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
         * Сохранить шаг тутора
         * @param int $userId
         * @param string $awardId
         * @return Response
         */
        public function saveAward($userId, $awardId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, award_id, create_date)
                VALUES
                    (:user_id, :award_id, CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':award_id'   => $awardId
            ));

            $err = $query->errorInfo();
            if($err[1] != null || $query->rowCount() < 1){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

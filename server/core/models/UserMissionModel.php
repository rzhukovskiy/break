<?php
    /**
     * работа с user_tutorial таблицей. прогресс в туториале
     */
    class UserMissionModel extends BaseModel {
        protected $_table = 'user_mission';

        /**
         * Создать самого себя
         *
         * @return UserMissionModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список шагов тутора у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserMissionListByUserId($userId) {
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
         * @param string $missionId
         * @return Response
         */
        public function getUserMissionByUserIdAndMissionId($userId, $missionId) {
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
                    mission_id = :mission_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':mission_id' => $missionId,
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
         * @param string $missionId
         * @return Response
         */
        public function saveMission($userId, $missionId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, mission_id, create_date)
                VALUES
                    (:user_id, :mission_id, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    create_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':mission_id'   => $missionId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

<?php
    /**
     * работа с user_tutorial таблицей. прогресс в туториале
     */
    class UserTutorialModel extends BaseModel {
        protected $_table = 'user_tutorial';

        /**
         * Создать самого себя
         *
         * @return UserTutorialModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список шагов тутора у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserTutorialListByUserId($userId) {
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
         * @param string $tutorialId
         * @return Response
         */
        public function getUserTutorialByUserIdAndTutorialId($userId, $tutorialId) {
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
                    tutorial_id = :tutorial_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':tutorial_id' => $tutorialId,
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
         * @param string $tutorialId
         * @return Response
         */
        public function saveTutorial($userId, $tutorialId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, tutorial_id, create_date)
                VALUES
                    (:user_id, :tutorial_id, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    create_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':tutorial_id'      => $tutorialId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

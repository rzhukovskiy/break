<?php
    /**
     * работа с user_scores таблицей. очки в мини-играх
     */
    class UserLogModel extends BaseModel {
        protected $_table = 'user_log';

        /**
         * Создать самого себя
         *
         * @return UserLogModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Все сообщения
         * @return Response
         */
        public function getUserLogList() {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    *
                FROM
                    ' . $this->_table;
            $query = $dataDb->prepare($sql);
            $query->execute();

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            $response->setData($query->fetchAll(PDO::FETCH_ASSOC));

            $this->deleteUserLogList();

            return $response;
        }

        /**
         * Очки в разных играх у определенного пользователя
         * @return Response
         */
        public function deleteUserLogList() {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'DELETE FROM
                    ' . $this->_table . '
                 WHERE
                     create_date < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)';
            $query = $dataDb->prepare($sql);
            $query->execute();

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Сохранить очки
         * @param int $userId
         * @param string $nickname
         * @param string $message
         * @return Response
         */
        public function saveUserLog($userId, $nickname, $message) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, nickname, message, create_date)
                VALUES
                    (:user_id, :nickname, :message, CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'       => $userId,
                ':nickname'      => $nickname,
                ':message'       => $message
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

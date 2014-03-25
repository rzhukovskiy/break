<?php
    /**
     * работа с user_scores таблицей. очки в мини-играх
     */
    class UserEventModel extends BaseModel {
        protected $_table = 'user_event';

        /**
         * Создать самого себя
         *
         * @return UserEventModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Очки в разных играх у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserEventListByUserId($userId) {
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
         * Очки в разных играх у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function checkUserEventListByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                    ' . $this->_table . '
                SET
                    checked = 1
                WHERE
                    user_id = :user_id';
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
         * Сохранить очки
         * @param int $userId
         * @param string $eventType
         * @param string $objectId
         * @return Response
         */
        public function saveUserEvent($userId, $eventType, $objectId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, event_type, object_id, create_date)
                VALUES
                    (:user_id, :event_type, :object_id, CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'         => $userId,
                ':event_type'      => $eventType,
                ':object_id'       => $objectId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

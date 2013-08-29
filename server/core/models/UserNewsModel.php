<?php
    /**
     * работа с user_news таблицей. новинки, принадлежащие юзерам
     */
    class UserNewsModel extends BaseModel {
        protected $_table = 'user_news';

        /**
         * Создать самого себя
         *
         * @return UserNewsModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список новинок у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserNewsListByUserId($userId) {
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
         * Убрать новинку
         * @param int $userId
         * @param string $ids
         * @return Response
         */
        public function removeUserNews($userId, $ids) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'DELETE FROM
                    ' . $this->_table . '
                WHERE
                    user_id = :user_id AND
                    item_id IN ("' . str_replace(',', '","', $ids) . '")';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response->setData(array('user_item_id' => $dataDb->lastInsertId()));
        }

        /**
         * Добавить новинку
         * @param int $userId
         * @param string $itemId
         * @return Response
         */
        public function addUserNews($userId, $itemId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT IGNORE INTO
                    ' . $this->_table . '
                    (user_id, item_id, create_date)
                VALUES
                    (:user_id, :item_id, CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':item_id'      => $itemId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response->setData(array('user_item_id' => $dataDb->lastInsertId()));
        }
    }

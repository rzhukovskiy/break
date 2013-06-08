<?php
    /**
     * Работа с таблицей user_info
     */
    class UserInfoModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return UserInfoModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Инфо пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserInfoByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    user_info
                WHERE
                    user_id = :user_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
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
         * Создать нового пользователя с указанным ID
         * @param int $userId
         * @return Response
         */
        public function addUserInfoByUserId($userId, $info) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $sql = 'INSERT INTO user_info
                    (user_id, email, name, gender, locale)
                VALUES
                    (:user_id, :email, :name, :gender, :locale)';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'  => $userId,
                ':email'    => $info['email'],
                ':name'     => isset($info['name']) ? $info['name'] : null,
                ':gender'   => isset($info['gender']) ? $info['gender'] : null,
                ':locale'   => isset($info['locale']) ? $info['locale'] : null
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($db->lastInsertId());
            }
            return $response;
        }
    }

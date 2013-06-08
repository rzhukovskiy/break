<?php
    /**
     * Работа с таблицей request - запросы пользователей
     */
    class RequestModel extends BaseModel {
        private $_table = 'request';

        /**
         * Создать самого себя
         *
         * @return RequestModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить запрос по ID
         * @param int $requestId
         * @param int $userId
         * @return Response
         */
        public function getRequestByRequestId($requestId, $userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . '
                    WHERE
                        id = :request_id AND
                        user_id_to = :user_id_to
                    LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':request_id' => $requestId,
                ':user_id_to' => $userId
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
         * Получить список запросов от юзера
         * @param int $userId
         * @return Response
         */
        public function getRequestListByUserFromId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE user_id_from = :user_id_from';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id_from' => $userId
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
         * Получить список запросов к пользователю
         * @param int $userId
         * @return Response
         */
        public function getRequestListByUserToId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE user_id_to = :user_id_to';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id_to' => $userId
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
         * @param $userIdFrom
         * @param $userIdTo
         * @param $dateFrom
         * @param $dateTo
         * @param $type
         * @return Response
         */
        public function getRequestListByDateAndType($userIdFrom, $userIdTo, $dateFrom, $dateTo, $type) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM
                        ' . $this->_table . '
                    WHERE
                        user_id_from = :user_id_from AND
                        user_id_to = :user_id_to AND
                        type = :type AND
                        create_date >= :date_from AND
                        create_date < :date_to';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id_from' => $userIdFrom,
                ':user_id_to'   => $userIdTo,
                ':date_from'    => $dateFrom,
                ':date_to'      => $dateTo,
                ':type'         => $type
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
         * @param $userIdFrom
         * @param $userIdTo
         * @param $type
         * @param $objectId
         * @return Response
         */
        public function getRequestListByRecipientAndTypeAndObjectId($userIdFrom, $userIdTo, $type, $objectId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM
                        ' . $this->_table . '
                    WHERE
                        user_id_from = :user_id_from AND
                        user_id_to = :user_id_to AND
                        type = :type AND
                        object_id = :object_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id_from' => $userIdFrom,
                ':user_id_to'   => $userIdTo,
                ':type'         => $type,
                ':object_id'    => $objectId
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
         * @param $userIdFrom
         * @param $type
         * @param $objectId
         * @return Response
         */
        public function getAcceptedRequestListByTypeAndObjectId($userIdFrom, $type, $objectId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql = 'SELECT * FROM
                        ' . $this->_table . '
                    WHERE
                        user_id_from = :user_id_from AND
                        type = :type AND
                        object_id = :object_id AND
                        status = "accepted"';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id_from' => $userIdFrom,
                ':type'         => $type,
                ':object_id'    => $objectId
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
         * Сохранить запрос
         * @param array $data
         * @return Response
         */
        public function saveRequest($data) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            //проверяем легальность запросов
            if(!$this->_saveValidation($data)) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong request');
            }

            $sql = 'INSERT INTO
                        ' . $this->_table . '
                        (id, user_id_from, user_id_to, type, object_id, status, modify_date)
                    VALUES
                        (:id, :user_id_from, :user_id_to, :type, :object_id, "sent", CURRENT_TIMESTAMP)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':id'           => $data['id'],
                ':user_id_from' => $data['user_id_from'],
                ':user_id_to'   => $data['user_id_to'],
                ':type'         => $data['type'],
                ':object_id'    => $data['object_id']
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * @param $requestId
         * @param $userId
         * @return Response
         */
        public function acceptRequest($requestId, $userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $request = $this->getRequestByRequestId($requestId, $userId);
            if($request->isError()) {
                return $request;
            }
            $request = $request->getData();

            //проверяем легальность запросов
            if(!$this->_acceptValidation($request)) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong request');
            }

            $sql = 'UPDATE
                        ' . $this->_table . '
                    SET
                        status = "accepted",
                        modify_date = CURRENT_TIMESTAMP
                    WHERE
                        id = :id AND
                        user_id_to = :user_id_to';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':id'           => $requestId,
                ':user_id_to'   => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * Проверка данных перед сохранением
         * @param array $requestData
         * @return bool
         */
        private function _saveValidation($requestData) {
            switch($requestData['type']) {
                case 'gift':
                    $requestList = $this->getRequestListByDateAndType(
                        $requestData['user_id_from'],
                        $requestData['user_id_to'],
                        date('Y-m-d 00:00:00', time()),
                        date('Y-m-d 00:00:00', time() + 24 * 3600),
                        $requestData['type']);

                    if($requestList->getData()) {
                        return false;
                    }
                    break;
                case 'location':
                case 'item':
                    $requestList = $this->getRequestListByRecipientAndTypeAndObjectId(
                        $requestData['user_id_from'],
                        $requestData['user_id_to'],
                        $requestData['type'],
                        $requestData['object_id']);

                    if($requestList->getData()) {
                        return false;
                    }
                    break;
                default:
                    return true;
            }
        }

        /**
         * Проверка данных перед принятием
         * @param array $requestData
         * @return bool
         */
        private function _acceptValidation($requestData) {
            switch($requestData['type']) {
                case 'gift':
                    if($requestData['status'] != 'sent') {
                        return false;
                    }
                    $response = UserModel::getInstance()->giveAward($requestData['user_id_to'], $requestData['object_id']);
                    if($response->isError()) {
                        return false;
                    }
                    return true;
                    break;
                case 'item':
                    if($requestData['status'] != 'sent') {
                        return false;
                    }
                    $response = UserItemModel::getInstance()->addUserItem($requestData['user_id_to'], $requestData['object_id']);
                    if($response->isError()) {
                        return false;
                    }
                    return true;
                    break;
                default:
                    return true;
            }
        }
    }

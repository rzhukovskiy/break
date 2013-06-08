<?php
    /**
     * работа с user_location таблицей. открытые локации
     */
    class UserLocationModel extends BaseModel {
        private $_table = 'user_location';
        /**
         * Создать самого себя
         *
         * @return UserLocationModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список открытых пользователю локаций
         * @param int $userId
         * @return Response
         */
        public function getUserLocationListByUserId($userId) {
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
         * Получить конкретную локацию у конкретного пользователя
         * @param int $userId
         * @param string $locationId
         * @return Response
         */
        public function getUserLocationByUserIdAndLocationId($userId, $locationId) {
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
                    location_id = :location_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':location_id' => $locationId,
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
         * Открыть пользователю указанную локацию за ключи
         * @param int $userId
         * @param string $locationId
         * @return Response
         */
        public function openUserLocationForKeys($userId, $locationId) {
            $location = LocationModel::getInstance()->getLocationByLocationId($locationId);
            if($location->isError()) {
                return $location;
            }
            $location = $location->getData();

            if($location['keys']) {
                $keyList = explode(';', $location['keys']);
                foreach($keyList as $keyData) {
                    list($keyId, $amount) = explode(' ', $keyData);
                    $response = UserKeyModel::getInstance()->takeUserKey($userId, $keyId, $amount);
                    if($response->isError()) {
                        return $response;
                    }
                }
            }

            return $this->addUserLocation($userId, $locationId);
        }

        /**
         * Открыть пользователю указанную локацию за друзей
         * @param int $userId
         * @param string $locationId
         * @return Response
         */
        public function openUserLocationForFriends($userId, $locationId) {
            $response = new Response();
            $location = LocationModel::getInstance()->getLocationByLocationId($locationId);
            if($location->isError()) {
                return $location;
            }
            $location = $location->getData();

            if($location['friends']) {
                $acceptedRequestList = RequestModel::getInstance()->getAcceptedRequestListByTypeAndObjectId(
                    $userId,
                    'location',
                    $locationId);
                if($acceptedRequestList->isError()) {
                    return $acceptedRequestList;
                }
                if(!$acceptedRequestList->getData() || count($acceptedRequestList->getData()) < $location['friends']) {
                    return $response->setCode(Response::CODE_WRONG_DATA)->setError('Not enough friends');
                }
            } else {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Can`t be open for friends');
            }

            return $this->addUserLocation($userId, $locationId);
        }

        /**
         * Добавить пользователю указанную локацию
         * @param int $userId
         * @param string $locationId
         * @param string $status
         * @return Response
         */
        public function addUserLocation($userId, $locationId, $status = 'open') {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, location_id, status, modify_date)
                VALUES
                    (:user_id, :location_id, :status, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    status = :status,
                    modify_date = CURRENT_TIMESTAMP';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':location_id'  => $locationId,
                ':status'       => $status
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }

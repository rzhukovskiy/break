<?php
    /**
     * Работа с таблицей location - локации в игре
     */
    class LocationModel extends BaseModel {
        private $_table = 'location';

        /**
         * Создать самого себя
         *
         * @return LocationModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить локацию по ID
         * @param string $locationId
         * @return Response
         */
        public function getLocationByLocationId($locationId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id = :location_id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':location_id' => $locationId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }
    }

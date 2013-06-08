<?php
    /**
     * Работа с таблицей collection - возможные коллекции для сбора игроком
     */
    class CollectionModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return CollectionModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить коллекцию по локации
         * @param string $locationId - ID локации
         * @return Response
         */
        public function getCollectionByLocationId($locationId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM collection WHERE location_id = :location_id LIMIT 1';
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

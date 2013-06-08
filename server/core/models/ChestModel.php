<?php
    /**
     * Работа с таблицей chest - сундуки
     */
    class ChestModel extends BaseModel {
        private $_table = 'chest';

        /**
         * Создать самого себя
         *
         * @return ChestModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить сундук по ID
         * @param string $chestId
         * @return Response
         */
        public function getChestByChestId($chestId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id = :chest_id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':chest_id' => $chestId
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

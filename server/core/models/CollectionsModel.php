<?php
/**
     * Работа с таблицей amulet - амулеты в игре
     */
    class CollectionsModel extends BaseModel {
        protected $_table = 'collections';

        /**
         * Создать самого себя
         *
         * @return CollectionsModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить коллекционный предмет по шансу
         * @param int $chance
         * @return Response
         */
        public function getCollectionsIdByChance($chance) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT
                        *
                    FROM '
                        . $this->_table .
                    ' WHERE
                        chance > :chance
                    ORDER BY chance
                    LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':chance' => $chance
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

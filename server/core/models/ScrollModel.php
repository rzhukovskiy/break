<?php
    /**
     * Работа с таблицей scroll - свитки в игре
     */
    class ScrollModel extends BaseModel {
        private $_table = 'scroll';

        /**
         * Создать самого себя
         *
         * @return ScrollModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить свиток по ID
         * @param string $scrollId
         * @return Response
         */
        public function getScrollByScrollId($scrollId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id = :scroll_id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':scroll_id' => $scrollId
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
         * Получить свиток по количеству звезд
         * @param int $stars
         * @return Response
         */
        public function getScrollByStarsAmount($stars) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE stars < :stars ORDER BY stars DESC LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':stars' => $stars
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

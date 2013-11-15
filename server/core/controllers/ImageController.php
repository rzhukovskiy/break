<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class ImageController extends BaseController {
        /** @var bool */
        protected $_withoutChecking = true;

        /**
         * Основная страница
         */
        public function saveAction() {
            $response = new Response();
            $image = $GLOBALS["HTTP_RAW_POST_DATA"];

            $filename = time() . '.png';
            $fullFilePath = "../photos/" . $filename;

            $handle=fopen($fullFilePath,"w");

            fwrite($handle,$image);
            fclose($handle);

            $curl = curl_init($this->getRequest()->getParam('upload_url', 1));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('file1' => '@' . $fullFilePath));
            $response->setData(array('result' => curl_exec($curl)))->send();
        }
    }

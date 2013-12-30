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
            $dataArray = json_decode(curl_exec($curl), true);

            $dataArray['response_code'] = count($dataArray['photos_list']) > 0 ? 1 : 4;

            unlink($fullFilePath);
            $response->setData($dataArray);
            $response->send();
        }
    }

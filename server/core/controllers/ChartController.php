<?php
    /**
     * Контроллер отвечающий за стату
     */
    class ChartController extends BaseController {
        /** @var bool */
        protected $_withoutChecking = true;

        /**
         * Основная страница
         */
        public function indexAction() {
            $levelList = UserModel::getInstance()->getUserLevelList()->getData();
            $musicList = UserSettingsModel::getInstance()->getUserMusicList()->getData();
            $sfxList = UserSettingsModel::getInstance()->getUserSfxList()->getData();
            $coinsList = UserModel::getInstance()->getUserCoinsList()->getData();
            $bucksList = UserModel::getInstance()->getUserBucksList()->getData();

            $this->loadView('chart', array(
                'levelList'         => $levelList,
                'musicList'         => $musicList,
                'sfxList'           => $sfxList,
                'coinsList'         => $coinsList,
                'bucksList'         => $bucksList,
            ));
        }
    }

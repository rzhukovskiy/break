<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class IndexController extends BaseController {
        /**
         * Основная страница
         */
        public function indexAction() {
            $fbParams = $this->_globals->getParam('facebook');
            $redirectUrl = $this->_social->getAuthUrl(array(
                'scope' => $fbParams['scope'],
                'redirect_uri' => 'http://apps.facebook.com/' . $fbParams['namespace'] . '/'));

            $this->loadView('index', array(
               'url'        => $redirectUrl,
               'facebook'   => $fbParams,
               'user'       => $this->_social->getUserId()
            ));
        }
    }

<?php
/**
 * LeanSwift eConnect Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the LeanSwift eConnect Extension License
 * that is bundled with this package in the file LICENSE.txt located in the
 * Connector Server.
 *
 * DISCLAIMER
 *
 * This extension is licensed and distributed by LeanSwift. Do not edit or add
 * to this file if you wish to upgrade Extension and Connector to newer
 * versions in the future. If you wish to customize Extension for your needs
 * please contact LeanSwift for more information. You may not reverse engineer,
 * decompile, or disassemble LeanSwift Connector Extension (All Versions),
 * except and only to the extent that such activity is expressly permitted by
 * applicable law not withstanding this limitation.
 *
 * @copyright   Copyright (c) 2019 LeanSwift Inc. (http://www.leanswift.com)
 * @license     https://www.leanswift.com/end-user-licensing-agreement
 */

namespace LeanSwift\Login\Helper;

use LeanSwift\Econnect\Helper\Secure;

class AuthClient extends Secure
{

    const XML_PATH_WEB_SERVICE_URL = 'leanswift_login/authentication/service_url';

    const XML_PATH_WEB_SERVICE_CLIENTID = 'leanswift_login/authentication/web_service_clientid';

    const XML_PATH_WEB_SERVICE_CLIENTSECRET = 'leanswift_login/authentication/web_service_clientsecret';

    const XML_PATH_DOMAIN = 'leanswift_login/authentication/domain_name';

    const XML_PATH_ENABLE = 'leanswift_login/authentication/enable_login';

    /**
     * Get Login API Client Id
     *
     * @param null $storeId
     *
     * @return mixed|string
     */
    public function getClientId($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEB_SERVICE_CLIENTID,
            $this->_dataHelper->getStoreScope(),
            $storeId);
    }

    /**
     * Get Login API Client Secret
     *
     * @param null $storeId
     *
     * @return string
     */
    public function getClientSecret($storeId = null)
    {
        return $this->_encryptorInterface->decrypt($this->scopeConfig->getValue(
            self::XML_PATH_WEB_SERVICE_CLIENTSECRET,
            $this->_dataHelper->getStoreScope(),
            $storeId
        ));
    }

    public function getOauthLink()
    {
        $link = $this->scopeConfig->getValue(self::XML_PATH_WEB_SERVICE_URL);
        $clientId = $this->getClientId();
        $param = "authorization.oauth2?client_id=$clientId&response_type=code";
        $oauthLink = $link . $param;
        return $oauthLink;
    }

    public function getTokenLink()
    {
        $link = $this->scopeConfig->getValue(self::XML_PATH_WEB_SERVICE_URL);
        $oauthLink = $link . 'token.oauth2';
        return $oauthLink;
    }

    public function logger()
    {
        return $this->_dataHelper;
    }

    public function getDomain()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DOMAIN);
    }

    public function isEnable()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE);
    }
}
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

namespace LeanSwift\Login\Model\Subscriber;

use LeanSwift\Econnect\Api\SubscriberInterface;
use LeanSwift\Econnect\Helper\Ion;
use LeanSwift\Econnect\Helper\Product as ProductHelper;
use LeanSwift\Econnect\Model\Catalog\Ion\CustomerPrice;
use LeanSwift\Econnect\Model\Catalog\Ion\ProductStock;
use LeanSwift\Econnect\Model\Catalog\Ion\SaveProduct;
use LeanSwift\Econnect\Model\Customer\Ion\Import;
use LeanSwift\Econnect\Model\ResourceModel\NonStockItems;
use LeanSwift\Econnect\Model\Sales\Ion\Import as SalesIonImport;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Xml\Parser;
use LeanSwift\Login\Helper\Erpapi as LoginHelper;
use LeanSwift\Login\Helper\Xpath;
use LeanSwift\Login\Helper\Constant;
use LeanSwift\Econnect\Api\MessageInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use LeanSwift\Econnect\Model\Subscriber\IonAbstractModel;

/**
 * Class UserRoles
 *
 * @package LeanSwift\Login\Model\Subscriber
 */
class UserRoles extends IonAbstractModel implements SubscriberInterface
{

    /**
     * @var Data
     */
    protected $apihelper;

    /**
     * @var XMLParser
     */
    protected $_xmlParser;

    /**
     * @var Ion
     */
    protected $_helper;

    /**
     * UserRoles constructor.
     *
     * @param Ion         $helperData
     * @param Parser      $parser
     * @param LoginHelper $loginhelper
     */
    public function __construct(
        Ion $helperData,
        Parser $parser,
        LoginHelper $loginhelper

    ) {
        $this->apihelper = $loginhelper;
       $this->_xmlParser = $parser;
       $this->_helper = $helperData;
    }


    /**
     * Process the message from Queue
     *
     * @param MessageInterface $message
     * @return bool|mixed
     */
    public function processMessage(MessageInterface $message)
    {
        $consumerMessage = $this->_helper->utf8_for_xml(base64_decode($message->getMessage()));

        try {
            $this->_xmlParser->loadXML($consumerMessage);
            //Parsing XML Data to Array
            $parsedXML = $this->_xmlParser->xmlToArray();

            if ($parsedXML) {
                $atpQueue = [Constant::SyncLSUserRoles];
                $atpQueueData = $this->getDataFromParsedXML($atpQueue, $parsedXML);
                $dataAreaSection = $this->getDataArea($atpQueueData);

                $bodType = [Constant::Sync];
                $bodData = $this->getDataFromParsedXML($bodType, $dataAreaSection);
                $actionCode = $this->getActionCode($bodData);
                if ($actionCode == 'Delete') {
                    return false;
                }
                $dataArea = [Constant::LSUserRoles];
                $userResponseData = $this->getDataFromParsedXML($dataArea, $dataAreaSection);
                //Prepare Stock Data
                $username = $userResponseData['LSUserRolesHeader']['DocumentID']['ID']['_value'];
                $userData = $this->_prepareData($userResponseData);
                $this->apihelper->updateuser($username, $userData);
            }
        } catch (\Exception $e) {
            $this->_helper->writeLog($e->getMessage(), false, null, 'catalog');
            return false;
        }
    }

    /**
     * Prepares data from AvailableToPromise BOD
     *
     * @param $atpData
     * @return mixed
     */
    public function _prepareData($userRoleData)
    {
        $data = $userRoleData['LSUserRoleList'];
        return $this->_helper->getSerializeObject()->serialize($data);
    }
}
<?php

/**
 * IndexController.php
 */
class MailUp_MailUpSync_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        $config = Mage::getModel('mailup/config');

        parent::preDispatch();

        return $this;
    }

    /**
     * Default Action
     */
    public function indexAction()
    {
    }

    public function showAction()
    {
    }
}

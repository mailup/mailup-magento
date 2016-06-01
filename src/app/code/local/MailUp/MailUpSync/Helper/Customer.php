<?php
/**
 * Customer helper methods for MailUp
 */
class MailUp_MailUpSync_Helper_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Check whether customer attribute is on ignore list
     *
     * @param $attr string $attr Name of attribute
     * @return bool
     */
    public function isAttrIgnored($attr)
    {
        $attrs = self::_getAttrsIgnored();

        return isset($attrs[$attr]);
    }

    /**
     * All standard attributes as a hash mapped to true for easy testing
     * Note that this does not include reward attributes for EE, so these will come out as part of customer attts
     *
     * @return array
     */
    protected static function _getAttrsIgnored()
    {
        static $attrs = array(
            'confirmation' => true,
            'created_at' => true,
            'created_in' => true,
            'default_billing' => true,
            'default_shipping' => true,
            'disable_auto_group_change' => true,
            'dob' => true,
            'email' => true,
            'firstname' => true,
            'gender' => true,
            'group_id' => true,
            'lastname' => true,
            'middlename' => true,
            'password_hash' => true,
            'prefix' => true,
            'rp_token' => true,
            'rp_token_created_at' => true,
            'store_id' => true,
            'suffix' => true,
            'taxvat' => true,
            'website_id' => true
        );

        return $attrs;
    }

    /**
     * Get customer attribute collection with only custom attributes
     *
     * @return Varien_Data_Collection_Db
     */
    public function getCustomCustomerAttrCollection()
    {
        $attrs = self::_getAttrsIgnored();

        $customerAttributes = Mage::getResourceModel('customer/attribute_collection')
            ->addFieldToFilter('attribute_code', array('nin' => array_keys($attrs)));

        return $customerAttributes;
    }

}

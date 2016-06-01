<?php
/**
 * Log.php
 * 
 * @method  string  getType()
 * @method  void    setType(string $type)
 * @method  string  getStatus()
 * @method  void    setStatus(string $type)
 * @method  int     getStoreId()
 * @method  void    setStoreId(int $storeId)
 * @method  int     getJobId()
 * @method  void    setJobId(int $jobId)
 */
class MailUp_MailUpSync_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    const TYPE_ERROR = 'ERROR';
    const TYPE_DEBUG = 'DEBUG';
    const TYPE_CRON = 'CRON';
    const TYPE_WARN = 'WARNING';
    
    protected function _construct()
    {
        $this->_init("mailup/log", "id");
    }
}
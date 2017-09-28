CHANGELOG
=========
(date follow ISO format - 06 is for June)

## [2.8.0]

### Changed
- [fix] refactoring class with Magento Coding Standard
- [imp] refactoring static class in Helper
- [imp] remove unused class and files
- [fix] remove "php include" and move the class in lib folder
  * Mailup_MailUpWsImport
  * Mailup_MailUpWsSend
- [fix] polish class

## [2.7.7]

### Changed
- add compatibility for php7 (thanks to [azambon](https://github.com/azambon))

## 2.7.6 - 11/09/2017
- [fix] admin config | close save config only to mailup section
- [fix] admin config | refactoring field check
- [dev] admin config | add skip test console url option
- [imp] gitignore | move IDE project ignore to global [https://help.github.com/articles/ignoring-files/#create-a-global-gitignore](https://help.github.com/articles/ignoring-files/#create-a-global-gitignore)

## 2.7.5 - 10/07/2017
- [dev] ip filter | send client ip for spam checking
- [fix] SUPEE-6788 | Fix adminhtml url

## 2.7.4
- [fix] APPSEC-1034 magento patch

## 2.7.3
- [fix] SUPEE-6788 | Fix access for non-admin user to config section

## 2.7.2 - Improvements to auto-sync and performance
* Resolved issues with auto-sync feature
* Improved auto-sync performance 
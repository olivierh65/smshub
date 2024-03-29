<?php

require_once 'smshub.civix.php';

use CRM_Smshub_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function smshub_civicrm_config(&$config): void {
  _smshub_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function smshub_civicrm_install(): void {

  $groupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'sms_provider_name', 'id', 'name');
  $params  =
    array(
      'option_group_id' => $groupID,
      'label' => 'SMSHub',
      'value' => 'ol65.smshub',
      'name'  => 'smshub',
      'is_default' => 1,
      'is_active'  => 1,
      'version'    => 3,
    );

  civicrm_api3('option_value', 'create', $params);
  _smshub_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function smshub_civicrm_enable(): void {

  $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', 'smshub', 'id', 'name');
  if ($optionID)
    CRM_Core_BAO_OptionValue::setIsActive($optionID, TRUE);

  $filter    =  array('name' => 'ol65.smshub');
  $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
  if ($Providers) {
    foreach ($Providers as $key => $value) {
      CRM_SMS_BAO_Provider::setIsActive($value['id'], TRUE);
    }
  }

  _smshub_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function smshub_civicrm_postInstall() {
  _smshub_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function smshub_civicrm_uninstall() {

  $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', 'smshub', 'id', 'name');
  if ($optionID)
    // CRM_Core_BAO_OptionValue::del($optionID);
    CRM_Core_BAO_OptionValue::deleteRecord(['id' => $optionID]);

  $filter    =  array('name'  => 'ol65.smshub');
  $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
  if ($Providers) {
    foreach ($Providers as $key => $value) {
      // CRM_SMS_BAO_Provider::del($value['id']);
      CRM_SMS_BAO_Provider::deleteRecord(['id' => $value['id']]);
    }
  }

  _smshub_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function smshub_civicrm_disable() {

  $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', 'smshub', 'id', 'name');
  if ($optionID)
    CRM_Core_BAO_OptionValue::setIsActive($optionID, FALSE);

  $filter    =  array('name' =>  'ol65.smshub');
  $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
  if ($Providers) {
    foreach ($Providers as $key => $value) {
      CRM_SMS_BAO_Provider::setIsActive($value['id'], FALSE);
    }
  }

  _smshub_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function smshub_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _smshub_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function smshub_civicrm_entityTypes(&$entityTypes) {
  _smshub_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Set a default value for an event price set field.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function smshub_civicrm_buildForm($formName, $form) {
  // increase message limit to accept tokens
  // TODO : check if it is possible to evaluate tokens before checking message length
  if ($formName == 'CRM_SMS_Form_Upload') {

    $providers = \Civi\Api4\Provider::get(false)
      ->addWhere('id', '=', $form->_defaultValues['sms_provider_id'])
      ->execute();
    if ($providers->first()['name'] == "ol65.smshub") {
      if ($form->_defaultValues['sms_provider_id'] == $providers->first()['id']) {
        if (isset($form->_mailingID)) {
          // remove header ans footer
          $results = \Civi\Api4\Mailing::update(false)
            ->addValue('header_id', '')
            ->addValue('footer_id', '')
            ->addWhere('id', '=', $form->_mailingID)
            ->execute();
        }

        $form->assign('max_sms_length', 4096);
        // PAs beau, mais redirige uniquement sur ma page.
        $form->_formRules = [];
        $form->addFormRule(['CRM_Smshub_Form_Upload', 'formRule'], $form);
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function smshub_civicrm_xmlMenu(&$files) {
  _smshub_civix_civicrm_xmlMenu($files);
}


function smshub_civicrm_alterMailParams(&$params, $context) {
  $aa=$context;
}

function smshub_civicrm_alterMailer(&$mailer, $driver, $params) {
  $aa=$mailer;
}
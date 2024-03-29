<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'SmsMessage',
    'class' => 'CRM_Smshub_DAO_SmsMessage',
    'table' => 'civicrm_sms_message',
  ],
];

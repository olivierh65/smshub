<?php

class CRM_Smshub_Page_Status extends CRM_Core_Page {

  public function run() {

    if (isset($_POST['deviceId'])) {
      $deviceId = $_POST['deviceId'];
    }

    if (isset($_POST['messageId'])) {
      $messageId = $_POST['messageId'];
    }

    if (isset($_POST['status'])) {
      $status = $_POST['status'];
    }

    if (isset($_POST['action'])) {
      $action = $_POST['action'];
    }
    switch ($status) {
      case 'SENT':
        $status_id_name = 'Pending';
        break;
      case 'FAILED':
        $status_id_name = 'Unreachable';
        break;
      case 'DELIVERED':
        $status_id_name = 'Completed';
        break;
      default:
        $status_id_name = 'Scheduled';
        break;
    }

    if (isset($_POST['apiKey'])) {
      $apiKey = $_POST['apiKey'];
    } else {
      $apiKey = '';
    }

    $smsMessages = \Civi\Api4\SmsMessage::get(false)
      ->addSelect('mailing.id', 'mailing.sms_provider_id', 'provider.password')
      ->addJoin('Mailing AS mailing', 'LEFT', ['mailing.id', '=', 'mailing_id'])
      ->addJoin('Provider AS provider', 'LEFT', ['provider.id', '=', 'mailing.sms_provider_id'])
      ->addWhere('message_id', '=', $messageId)
      ->setLimit(25)
      ->execute();

    if ($smsMessages->first()['provider.password'] != $apiKey) {
      echo "Permission denied";
      http_response_code(403);
      CRM_Utils_System::civiExit();
    }

    if ($status == 'DELIVERED') {
      $results = \Civi\Api4\SmsMessage::update(false)
        ->addValue('is_delivered', TRUE)
        ->addValue('date_delivered', date("Y-m-d H:i:s"))
        ->addWhere('message_id', '=', $messageId)
        ->execute();
    }

    $results = \Civi\Api4\Activity::update(false)
      ->addValue('result', $status)
      ->addValue('status_id:name', $status_id_name)
      ->addValue('modified_date', date("Y-m-d H:i:s"))
      ->addWhere('location', '=', $messageId)
      ->execute();

    CRM_Utils_System::civiExit();
  }

  /**
   * What should happen if we want to reject the message without processing it.
   */
  protected function invalidMessage() {
    http_response_code(400);
    CRM_Utils_System::civiExit();
  }
}

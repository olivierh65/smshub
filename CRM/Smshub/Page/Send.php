<?php

class CRM_Smshub_Page_Send extends CRM_Core_Page {

  public function run() {

    $aa = $_REQUEST;
    if (isset($_REQUEST['apiKey'])) {
      $apiKey = $_REQUEST['apiKey'];
    } else {
      $apiKey = '';
    }

    $smsMessages = \Civi\Api4\SmsMessage::get(FALSE)
      ->addWhere('is_send', '=', FALSE)
      ->setLimit(5)
      ->execute();

    if ($smsMessages->count() == 0) {
      echo "";
      CRM_Utils_System::civiExit();
    }

    $mailings = \Civi\Api4\Mailing::get(false)
      ->addSelect('sms_provider_id.password')
      ->addWhere('id', '=', $smsMessages->first()['mailing_id'])
      ->execute();

    if ($mailings->first()['sms_provider_id.password'] != $apiKey) {
      echo "Permission denied";
      http_response_code(403);
      CRM_Utils_System::civiExit();
    }

    $messages = [];
    $ids = [];
    foreach ($smsMessages as $smsMessage) {
      $messages[] = [
        'message' => $smsMessage['message'],
        'number' => $smsMessage['number'],
        'messageId' => (string)$smsMessage['message_id'],
      ];
      $ids[] = $smsMessage['id'];
    }

    $aa = json_encode($messages, JSON_UNESCAPED_SLASHES);
    echo $aa;

    foreach ($smsMessages as $smsMessage) {
      $results = \Civi\Api4\SmsMessage::update(FALSE)
        ->addValue('is_send', TRUE)
        ->addValue('date_send', date("Y-m-d H:i:s"))
        ->addWhere('id', 'IN', $ids)
        ->execute();
    }

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

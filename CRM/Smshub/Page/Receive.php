<?php
use CRM_Smshub_ExtensionUtil as E;

class CRM_Smshub_Page_Receive extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Status1'));

    http_response_code(400);
    CRM_Utils_System::civiExit();
  }

}

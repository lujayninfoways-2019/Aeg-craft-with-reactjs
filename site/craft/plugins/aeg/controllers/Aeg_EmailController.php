<?php
namespace Craft;

use Guzzle\Http\Client;

class Aeg_EmailController extends BaseController {

  protected $allowAnonymous = array(
    'actionSendIngredients',
    'actionSendQuestion'
  );

  public function actionSendIngredients() {
    $this->requireAjaxRequest();
    $params = craft()->request->getRestParams();
    $referrer = craft()->request->getUrlReferrer();
    $uri = urldecode(ltrim(parse_url($referrer, PHP_URL_PATH), '/'));

    $this->validateRecaptcha($params);

    $result = craft()->aeg_email->sendIngredients($uri, $params);

    if (is_string($result) || !$result) {
      // error messages
      http_response_code(400);
      $this->returnErrorJson($result);
    }
    else {
      // success
      $this->returnJson(TRUE);
    }

  }

  public function actionSendQuestion() {
    $this->requireAjaxRequest();
    $params = craft()->request->getRestParams();

    $this->validateRecaptcha($params);

    $result = craft()->aeg_email->sendQuestion($params);

    if (is_string($result) || !$result) {
      // error messages
      http_response_code(400);
      $this->returnErrorJson($result);
    }
    else {
      // success
      $this->returnJson(TRUE);
    }
  }

  private function validateRecaptcha($params) {
      // validate recaptcha (see obj google account)
      $data = array(
          'secret' => '6LekQTIUAAAAANfIygS4EiRKfMFgDwWhNNwFCsWB',
          'response' => $params['g-recaptcha-response']
      );

      $client = new Client();
      $request = $client->post('https://www.google.com/recaptcha/api/siteverify', array(), $data);

      try {
          $response = $request->send();
          $json = $response->json();
      } catch (\Exception $e) {
          http_response_code(400);
          $this->returnErrorJson('captcha error');
      }

      if (empty($json['success']) || !$json['success']) {
          http_response_code(400);
          $this->returnErrorJson('invalid captcha');
      }
  }
}

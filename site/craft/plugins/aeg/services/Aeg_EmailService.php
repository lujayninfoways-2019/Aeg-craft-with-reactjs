<?php
namespace Craft;

class Aeg_EmailService extends BaseApplicationComponent {

  public function sendIngredients($uri, $params) {
    $globalSet = craft()->globals->getSetByHandle('contact');

    $criteria = craft()->elements->getCriteria(ElementType::Entry);
    $criteria->section = 'recipes';
    $criteria->uri = $uri;
    $criteria->limit = 1;
    $entry = $criteria->first();

    if (!$entry) {
      return 'recipe not found';
    }

    $email = $params['email'];
    $count = $params['serves'];
    $ingredients = $params['ingredients'];

    $variables = array(
        'recipe' => $entry->title,
        'count' => $count,
        'ingredients' => "* " . str_replace("\n\n", "\n* ", str_replace("\r", "", $ingredients))
    );

    // manually fetch template (craft sendEmailByKey won't transform markup...)
    $message = craft()->emailMessages->getMessage('aegIngredientsMail');
    $renderedBody = craft()->templates->renderString($message->body, $variables);

    $emailModel = new EmailModel();
    $emailModel->toEmail = $email;
    if ($globalSet->senderAddress) {
      $emailModel->fromEmail = $globalSet->senderAddress;
    }
    $emailModel->subject = 'Zutaten für ' . $entry->title;
    $emailModel->body = $renderedBody;

    return craft()->email->sendEmail($emailModel);
  }

  public function sendQuestion($params) {
    $globalSet = craft()->globals->getSetByHandle('contact');
    $email = $globalSet->questionFormAddress;

    $emailModel = new EmailModel();
    $emailModel->toEmail = $email;
    if ($globalSet->senderAddress) {
      $emailModel->fromEmail = $globalSet->senderAddress;
    }
    $emailModel->replyTo = $params['email'];
    $emailModel->subject = 'Kontaktanfrage von ' . ($params['name'] ?: $params['email']);
    $emailModel->body = $params['message'];

    return craft()->email->sendEmail($emailModel);
  }
}

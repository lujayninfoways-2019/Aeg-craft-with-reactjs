<?php
return array(
  '*' => array(
    'omitScriptNameInUrls' => true,
    'sendPoweredByHeader' => false,
    'devMode' => getenv("CRAFT_DEVMODE") == "true",
    'enableTemplateCaching' => getenv("CRAFT_DEVMODE") != "true"
  ),
  'siteUrl' => 'http://localhost:8092'
);
?>

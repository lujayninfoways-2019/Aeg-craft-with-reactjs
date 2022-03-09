<?php
/**
 * Database Configuration
 * You can see a list of the default settings in craft/app/etc/config/defaults/db.php
 */
$env = getenv("MYSQL_ENV") ?: "MYSQL_PORT";
$url = getenv($env) ?: getenv("CLEARDB_DATABASE_URL");
if (getenv("MYSQL_ENV_MYSQL_ALLOW_EMPTY_PASSWORD") == "yes") {
 $url = 'mysql://' . (getenv("MYSQL_USER") ?: 'root') . '@' . getenv("MYSQL_PORT_3306_TCP_ADDR") . ':' . getenv("MYSQL_PORT_3306_TCP_PORT") . '/' . getenv("MYSQL_ENV_MYSQL_DATABASE");
}
$url = $url ?: getenv("MYSQL_URL");
$url = parse_url($url ?: 'mysql://root@localhost');

return array(
  'server' => 'mysql',
  'port' => '3306',
  'user' =>  'craft',
  'password' =>  'mySecretPassword',
  'database' => 'craft',
  'tablePrefix' => 'craft_',
);
?>

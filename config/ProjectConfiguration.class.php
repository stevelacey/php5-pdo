<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration {
  public function setup() {
    $this->enableAllPluginsExcept('sfPropelPlugin');
  }

  public function configureDoctrine(Doctrine_Manager $manager) {
    $manager->registerConnectionDriver('mysql-alternative-pdo', 'Doctrine_Connection_Mysql_Alternative_PDO');
  }
}

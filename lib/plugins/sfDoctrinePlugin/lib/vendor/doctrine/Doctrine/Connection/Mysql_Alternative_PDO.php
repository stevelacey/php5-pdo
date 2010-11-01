<?php

class Doctrine_Connection_Mysql_Alternative_PDO extends Doctrine_Connection_Mysql {
    /**
     * Doctrine_Connection
     *
     * Swapped out a couple of calls to PDO for _PDO
     (
     * connect
     * connects into database
     *
     * @return boolean
     */
    public function connect() {
        if ($this->isConnected) {
            // Doctrine_Connection_Mysql
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            // End Doctrine_Connection_Mysql
            return false;
        }

        $event = new Doctrine_Event($this, Doctrine_Event::CONN_CONNECT);

        $this->getListener()->preConnect($event);

        $e     = explode(':', $this->options['dsn']);
        $found = false;

        if (extension_loaded('pdo')) {
            if (in_array($e[0], self::getAvailableDrivers())) {
                try {
                    $this->dbh = new _PDO($this->options['dsn'], $this->options['username'],
                                     (!$this->options['password'] ? '':$this->options['password']), $this->options['other']);

                    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    throw new Doctrine_Connection_Exception('PDO Connection Error: ' . $e->getMessage());
                }
                $found = true;
            }
        }

        if ( ! $found) {
            // Hack to force use of normal mysql adapter
            $class = 'Doctrine_Adapter_' . ucwords(strpos($e[0], '-') !== false ? substr($e[0], 0, strpos($e[0], '-')) : $e[0]);

            if (class_exists($class)) {
                $this->dbh = new $class($this->options['dsn'], $this->options['username'], $this->options['password'], $this->options);
            } else {
                throw new Doctrine_Connection_Exception("Couldn't locate driver named " . $e[0]);
            }
        }

        // attach the pending attributes to adapter
        foreach($this->pendingAttributes as $attr => $value) {
            // some drivers don't support setting this so we just skip it
            if ($attr == Doctrine_Core::ATTR_DRIVER_NAME) {
                continue;
            }
            $this->dbh->setAttribute($attr, $value);
        }

        $this->isConnected = true;

        $this->getListener()->postConnect($event);

        // Doctrine_Connection_Mysql
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        // End Doctrine_Connection_Mysql

        return true;
    }

    /**
     * returns an array of available PDO drivers
     */
    public static function getAvailableDrivers() {
        return _PDO::getAvailableDrivers();
    }
}

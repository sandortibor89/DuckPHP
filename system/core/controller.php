<?php
namespace core;

if (class_exists('\model\Authentication')) {
    class Controller extends \model\Authentication {

		protected $loaded;

        public function __construct() {
            parent::__construct();
            $backup = Router::get()['mysqlbackup'];
            if ($backup == 1) {
                $filename='database_backup_'.MYSQL_DATABASE.'_'.date('Y_m_d_H_i_s').'.sql';
                exec('mysqldump --user='.MYSQL_USERNAME.' --password='.MYSQL_PASSWORD.' --host='.MYSQL_HOST.' '.MYSQL_DATABASE.' > '.BACKUPS_DIR.DS.$filename);
            } elseif (file_exists(APP_SUB_DIR.DS.'all_controller_load.php')) {
				$this -> loaded = require_once(APP_SUB_DIR.DS.'all_controller_load.php');
			}
			echo 'ok';
        }
    }
} else {
    class Controller {

		protected $loaded;

        public function __construct() {
            $backup = Router::get()['mysqlbackup'];
            if ($backup == 1) {
                $filename='database_backup_'.MYSQL_DATABASE.'_'.date('Y_m_d_H_i_s').'.sql';
                exec('mysqldump --user='.MYSQL_USERNAME.' --password='.MYSQL_PASSWORD.' --host='.MYSQL_HOST.' '.MYSQL_DATABASE.' > '.BACKUPS_DIR.DS.$filename);
            } elseif (file_exists(APP_SUB_DIR.DS.'all_controller_load.php')) {
				$this -> loaded = require_once(APP_SUB_DIR.DS.'all_controller_load.php');
			}
        }
    }
}

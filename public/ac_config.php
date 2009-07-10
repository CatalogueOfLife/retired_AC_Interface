<?php
try {
    //Read settings file
    $app_config = parse_ini_file('../config/application.ini', true);
    if (! $app_config) {
        throw new Exception('Missing application configuration file');
    }
    //Mapping of configuration parameters    
    $sql_server = $app_config['resource.sb.params.host'];
    $sql_user_name = $app_config['resources.db.params.username'];
    $sql_password = $app_config['resources.db.params.password'];
    $sql_database = $app_config['resources.db.params.dbname'];
    $show_accepted_names_only = 
        (bool) $app_config['interface.options.showAcceptedNamesOnly'];
    $online_or_offline_version = 
        (bool) $app_config['interface.options.online'] ? 
    		'online' : 'offline';
    //Test connection to database
    if (! @mysql_connect($sql_server, $sql_user_name, $sql_password)) {
        throw new Exception(mysql_error());
    }
    if (! @mysql_select_db($sql_database)) {
        throw new Exception(mysql_error());
    }
    mysql_close();

} catch (Exception $e) {
    die('The interface could not be initialized: ERROR - ' 
        . $e->getMessage());
}
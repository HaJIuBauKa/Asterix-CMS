<?php

// До момента запуска ядра ошибки показываются, потом - в зависимости от режима test_mode
error_reporting( E_ALL^E_DEPRECATED^E_NOTICE^E_STRICT );
ini_set( "display_errors", "on" );

// Багфикс для серверов, не поддерживающих команду error_reporting
if( error_reporting() != 22519 )
	ini_set( "display_errors", "off" );

session_start();

// проверка версии PHP
$version = explode( '.', phpversion() );
$version = $version[0].'.'.$version[1];
if( $version < 5.4 ){
	print('Устаревшая версия PHP, необходима версия не менее 5.4.');
	exit();
}

ini_set( 'include_path', implode( ';', $config['path'] ) );

// Система контроля исключений работы системы
require_once($config['path']['core'] . '/classes/log.php');
$log = new log($config);

// Кеш
require_once($config['path']['core'] . '/classes/cache.php');
$cache = new cache($config, $log);
$cache->readCache();

// Объявленная переменная позволяет просто запустить ядро, без выполнения контроллеров
if( !$start_only ) {
	// Менеджер контроллеров
	require_once($config['path']['core'] . '/classes/controller_manager.php');
	$controller_manager = new controller_manager($config, $log, $cache);

	exit();

} else {
	require_once($config['path']['core'] . '/classes/model.php');
	$model = new model($config, $log, $cache);

}

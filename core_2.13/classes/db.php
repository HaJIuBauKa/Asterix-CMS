<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		�������� ��������� ����								*/
/*															*/
/*	������ ���� 2.0											*/
/*	������ ������� 1.00										*/
/*															*/
/*	Copyright (c) 2009  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 10 ������� 2009	����							*/
/*	�������������: 25 �������� 2009 ����					*/
/*															*/
/************************************************************/

global $config;

//�������������� ������� ��� ������
$supported_databases = array(
	false => array(
		'mysql' => 'mysql.php'
	),
	'ado' => array(
		'mysql' => 'adodb5/adodb.inc.php'
	)
);

//����� ���������� ������ ������
class database
{
	public function __construct($model)
	{
		$this->model = $model;
		$this->connection = false;
		$this->active_database = false;
	}
	
	public function activate()
	{
		mysql_select_db($this->name, $this->connection);
		$this->active_database = $this->name;
	}
	
}

//������������� ��� ������
$db = array();
foreach ($config['db'] as $name => $one) {
	if (IsSet($supported_databases[$one['lib_pack']][$one['type']])) {
		$config['db'][$name]['supported'] = true;
		
		require_once($config['path']['libraries'] . '/' . $supported_databases[$one['lib_pack']][$one['type']]);
		
		$n = $one['type'];
		
		//ADO
		if ($one['lib_pack'] == 'ado') {
			$db[$name] = ADONewConnection($one['type']);
			$db[$name]->SetFetchMode(ADODB_FETCH_ASSOC);
			$db[$name]->debug = false;
			
		//�� ADO
		} else {
			$db[$name] = new $n($model);
		}
		
		$db[$name]->PConnect($one['host'], $one['user'], $one['password'], $one['name']);
		if( !$db[$name]->connection ){
			print('������ ���������� � ����� ������.');
			exit();
		}
		
		$db[$name]->Execute('set character_set_client="utf8", character_set_results="utf8", collation_connection="utf8_general_ci"');
		
	} else {
		$config['db'][$name]['supported'] = false;
	}
}

return $db;

?>
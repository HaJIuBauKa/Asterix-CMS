<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		�������� ����������									*/
/*															*/
/*	������ ���� 2.0.b5										*/
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

class extention_default
{
	var $title = '����������, ������� ������ �� ������, � ���� ���������� ��� ����� ������ ����������';
	
	//������������� ����������
	public function __construct($db, $log)
	{
		$this->log = $log;
	}
	
	//������ ������
	public function onModuleStart()
	{
	}
	
	//������ �����������
	public function onControllerStart()
	{
	}
	
	//����� ����������� �������
	public function onSql($fields, $tables, $where = false, $group = false, $order = false, $limit = false, $query_type = 'getall')
	{
		return array(
			$fields,
			$tables,
			$where,
			$group,
			$order,
			$limit
		);
	}
	
	//��������� ��������� ���� � ������
	public function addFields()
	{
	}
	
	//����� ������� ������ � ������������
	public function onAssign()
	{
	}
	
	//����� ���������� �������
	public function onFetch()
	{
	}
}

?>
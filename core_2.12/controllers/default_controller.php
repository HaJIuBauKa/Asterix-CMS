<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		�������� �����������								*/
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

class default_controller
{
	public $vars = array();
	
	function __construct($model, $vars, $cache)
	{
		$this->model = $model;
		$this->vars  = $vars;
		$this->cache = $cache;
	}
	
	
}

?>
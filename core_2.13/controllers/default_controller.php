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
	
	//�������� ����������� JS-����������
	public function addJS($path, $params = false){
		$this->add['js'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	//�������� ����������� JS-����������
	public function addJSLib($path, $params = false){
		$this->add['js_lib'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	//�������� ����������� JS-����������
	public function addCSS($path, $params = false){
		$this->add['css'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}
	//�������� ����������� JS-����������
	public function addCSSLib($path, $params = false){
		$this->add['css_lib'][] = array(
			'path' => $path,
			'params' => $params,
		);
	}

	
}

?>
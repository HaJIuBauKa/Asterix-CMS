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
	public static $add;
	
	//����������, ������� ����� ���������� �� �����������
	public static $known_js = array(
		'lightbox' => 'http://src.sitko.ru/3.0/j/lightbox.js',
		'carousel' => 'http://src.sitko.ru/3.0/bootstrap/bootstrap-carousel.js',
	);
	public static $known_css = array(
		'lightbox' => 'http://src.sitko.ru/a/c/lightbox.css',
		'bootstrap' => 'http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css',
	);
	
	function __construct($model, $vars, $cache){
		$this->model = $model;
		$this->vars  = $vars;
		$this->cache = $cache;
	}
	
	//�������� ����������� JS-����������
	public function addJS($path, $params = false){
		self::$add['js_core'][] = array(
			'path' => $path,
			'params' => $params,
		);
		return self::$add;
	}

	//�������� ����������� JS-����������
	public function addCSS($path, $params = false){
		self::$add['css_core'][] = array(
			'path' => $path,
			'params' => $params,
		);
		return self::$add;
	}
	//�������� ����������� JS-����������
	public function addUserJS($vals, $params = false){
		if( substr_count($vals, ',') ){
			$vals = explode(',', $vals);
			foreach($vals as $i=>$val)
				$vals[$i] = trim($val);
		}else
			$vals = array($vals);
			
		foreach($vals as $i=>$val){
			if( IsSet( self::$known_js[$val] ) )
				$vals[$i] = self::$known_js[$val];
		}
		return $vals;
	}

	//�������� ����������� JS-����������
	public function addUserCSS($vals, $params = false){
		if( substr_count($vals, ',') ){
			$vals = explode(',', $vals);
			foreach($vals as $i=>$val)
				$vals[$i] = trim($val);
		}else
			$vals = array($vals);
			
		foreach($vals as $i=>$val){
			if( IsSet( self::$known_css[$val] ) )
				$vals[$i] = self::$known_css[$val];
		}
		return $vals;
	}

	
}

?>
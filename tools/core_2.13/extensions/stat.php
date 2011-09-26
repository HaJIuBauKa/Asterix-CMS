<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		����� ����� ���������� ������� � ������				*/
/*															*/
/*	������ ���� 2.06										*/
/*	������ ������� 0.1										*/
/*															*/
/*	Copyright (c) 2009  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 12 ������� 2010	����							*/
/*	�������������: 12 ������� 2010 ����						*/
/*															*/
/************************************************************/

require_once 'default.php';

class extention_stat extends extention_default
{

	public function __construct($model)
	{
		$this->model = $model;
		
		//������������ ������� �������� ����������
		$this->model->tmpl->register_modifier('count_view', array(
			$this,
			'countView'
		));
		
		//������������ ������� �������� ��������� �� �������
		$this->model->tmpl->register_modifier('count_click', array(
			$this,
			'countClick'
		));
		

	}
	
	//������������ ������� �������� ����������
	//��������� JavaScript, ������������ �������� � ���� �������, ������� ����� ������������� �� ������� /stat.php
	public function countView($value, $top, $anons = false, $label = false){//�������� ����� �������
		//�������� ���, ������� ����� �������� � ��������������� ��������
		$plus_code = '<img src="/stat.php?a=v&i='.$top.'|'.intval($anons).'&l='.$label.'" alt="stat" style="position:absolute; z-index:-1000; width:1px; height:1px;" />';
		//����������� ���
		$value .= $plus_code;
		//������
		return $value;		
	}
	
	//������������ ������� �������� ��������� �� �������
	//���������� ������ � ������, ������� ����� ����� �� /stat.php � ���������� ��������� ��� ��������� � ���� ��������� �����.
	public function countClick($value, $top, $label = false){//�������� ������
		//������� ������, ������� ����� ����� ����������
		$value = '/stat.php?a=c&i='.$top.'&l='.$label.'&h='.urlencode($value);
		//������
		return $value;		
	}

}

?>
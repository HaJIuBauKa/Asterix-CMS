<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		������� ������										*/
/*															*/
/*	������ ���� 2.04										*/
/*	������ ������� 1.00										*/
/*															*/
/*	Copyright (c) 2009  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 19 ����� 2010 ����								*/
/*	�������������: 19 ����� 2010 ����						*/
/*															*/
/************************************************************/

class filters
{
	private $jevix_ver = 'jevix-1.1';
	
	
	function __construct($model)
	{
		$this->model = $model;
	}
	
	//������ �������� ������ �� ������ HTML-����� � ������ ����.��������
	public function filterHtml($text)
	{
		//��������� �����
		include_once($this->model->config['path']['libraries'] . '/' . $this->jevix_ver . '/jevix.class.php');
		
		//��������������
		$jevix = new Jevix();
		
		// 1. ������������� ����������� ����. (��� �� ����������� ���� ��������� ������������.)
		$jevix->cfgAllowTags(array(
			'a',
			'img',
			'i',
			'b',
			'u',
			'em',
			'strong',
			'nobr',
			'li',
			'ol',
			'ul',
			'sup',
			'abbr',
			'pre',
			'acronym',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'adabracut',
			'br',
			'code'
		));
		
		// 2. ������������� ������� ����. (�� ������� ������������ ����)
		$jevix->cfgSetTagShort(array(
			'br',
			'img'
		));
		
		// 3. ������������� ������������������ ����. (� ��� ��� ����� ��������� �� HTML ��������)
		$jevix->cfgSetTagPreformatted(array(
			'pre'
		));
		
		// 4. ������������� ����, ������� ���������� �������� �� ������ ������ � ���������.
		$jevix->cfgSetTagCutWithContent(array(
			'script',
			'object',
			'iframe',
			'style'
		));
		
		// 5. ������������� ����������� ��������� �����. ����� ����� ������������� ���������� �������� ���� ����������.
		$jevix->cfgAllowTagParams('a', array(
			'title',
			'href'
		));
		$jevix->cfgAllowTagParams('img', array(
			'src',
			'alt' => '#text',
			'title',
			'align' => array(
				'right',
				'left',
				'center'
			),
			'width' => '#int',
			'height' => '#int',
			'hspace' => '#int',
			'vspace' => '#int'
		));
		
		
		// 6. ������������� ��������� ����� ���������� ��������������. ��� ��� �������� ��� �������� ����������.
		$jevix->cfgSetTagParamsRequired('img', 'src');
		$jevix->cfgSetTagParamsRequired('a', 'href');
		
		// 7. ������������� ���� ������� ����� ��������� ��� ���������
		//    cfgSetTagChilds($tag, $childs, $isContainerOnly, $isChildOnly)
		//       $isContainerOnly : ��� �������� ������ ����������� ��� ������ ����� � �� ����� ��������� ����� (�� ��������� false)
		//       $isChildOnly : ��������� ���� �� ����� �������������� ����� ����� ���������� ���� (�� ��������� false)
		//$jevix->cfgSetTagChilds('ul', 'li', true, false);
		
		// 8. ������������� �������� �����, ������� ����� ���������� �������������
		$jevix->cfgSetTagParamDefault('a', 'rel', null, true);
		//$jevix->cfgSetTagParamsAutoAdd('a', array('rel' => 'nofollow'));
		//$jevix->cfgSetTagParamsAutoAdd('a', array('name'=>'rel', 'value' => 'nofollow', 'rewrite' => true));
		
		$jevix->cfgSetTagParamDefault('img', 'width', '300px');
		$jevix->cfgSetTagParamDefault('img', 'height', '300px');
		//$jevix->cfgSetTagParamsAutoAdd('img', array('width' => '300', 'height' => '300'));
		//$jevix->cfgSetTagParamsAutoAdd('img', array(array('name'=>'width', 'value' => '300'), array('name'=>'height', 'value' => '300') ));
		
		// 9. ������������� ����������
		$jevix->cfgSetAutoReplace(array(
			'+/-',
			'(c)',
			'(r)'
		), array(
			'�',
			'�',
			'�'
		));
		
		// 10. �������� ��� ��������� ����� XHTML. (�� ��������� �������)
		$jevix->cfgSetXHTMLMode(true);
		
		// 11. �������� ��� ��������� ����� ������ �������� ����� �� ��� <br/>. (�� ��������� �������)
		$jevix->cfgSetAutoBrMode(true);
		
		// 12. �������� ��� ��������� ����� ��������������� ����������� ������. (�� ��������� �������)
		$jevix->cfgSetAutoLinkMode(true);
		
		// 13. ��������� ���������������� � ������������ ����
		$jevix->cfgSetTagNoTypography('code');
		
		// ����������, � ������� ����� ����������� ������
		$errors = null;
		
		// ������
		$result = $jevix->parse($text, $errors);
		
		// ������
		return $result;
	}
	
	
}

?>
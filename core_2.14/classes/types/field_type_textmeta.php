<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		��� ������ - ����-������ � ���������� ���� 			*/
/*															*/
/*	������ ���� 2.14										*/
/*	������ ������� 1.00										*/
/*															*/
/*	Copyright (c) 2012  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 13 ���� 2012 ����								*/
/*	�������������: 13 ���� 2012 ����						*/
/*															*/
/************************************************************/

class field_type_textmeta extends field_type_default{
	
	public $default_settings = array
	(
		'sid' 	=> false, 
		'title' => '����-������ � ���������� ���� ���� "���������� ��������"', 
		'value' => '', 
		'width' => '100%'
	);
	
	public $template_file = 'types/hidden.tpl';
	
	public function creatingString($name)
	{
		return '`' . $name . '` TEXT NOT NULL';
	}
	
	//�������������� �������� ��� SQL-�������
	public function toValue($value_sid, $values, $old_values = array(), $settings = false, $module_sid = false, $structure_sid = false){

		$meta = false;
		if( !IsSet( $settings['field'] ) )
			$settings['field'] = 'text';
	
		// ����, ������� ����� �������������
		$field_sid = $settings['field'];
		if( IsSet( model::$modules[ $module_sid ]->structure[ $structure_sid ]['fields'][ $field_sid ] ) )
		{
			$field = model::$modules[ $module_sid ]->structure[ $structure_sid ]['fields'][ $field_sid ];
			
			// ���� �� �������������-�� ����?
			if( IsSet( $values[ $field_sid ] ) )
			{
				
				// ������ � ������
				$meta['files'] = $this->getFiles( $values[ $field_sid ] );
				
				// ������ � �������
				$meta['links'] = $this->getLinks( $values[ $field_sid ] );
				
				$meta = serialize( $meta );
			}
			
		}
		
		// ������
		return $meta;
	}
	
	//�������� ���������� �������� �� �������� ��������
	public function getValueExplode($value, $settings = false, $record = array())
	{
		$result = false;
		if( $value )
			if( !is_array($value) )
			{
				$result = unserialize( htmlspecialchars_decode( $value ) );
				$result['old'] = $value;
			}
			
		return $result;
	}
	
	// ������� ���������� �������� ��� ������� ���������� �� �������� ��������
	public function getAdmValueExplode($value, $settings = false, $record = array())
	{
		return $this->getValueExplode($value, $settings, $record);
	}





	// ������ � ������
	private function getFiles( $value )
	{
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $value, $result);
		$result=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$result[0]);
		return $result;
	}
	
	// ������ � �������
	private function getLinks( $value )
	{
		preg_match_all("/<[Aa][ \r\n\t]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\n\r\t]*([^ \"'>\r\n\t#]+)[^>]*>/", $value, $result);
		return @$result[1];
	}
	
}

?>
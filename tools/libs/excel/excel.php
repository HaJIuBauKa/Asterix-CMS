<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		����� ������ � ��������� Microsoft Excel 97-2007	*/
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

class excel{
	var $title = 'ACMS Excel-������';
	var $title_shirt = 'Excel-������';
	var $sid = 'excel';
	var $version = '1.0';
	var $db = '';
	var $allowed_formats=array('xls'=>'2000','xlsx'=>'2007');

	var $module_working_mode='Light';// (Light|Professional)
	var $module_working_mode_title='�����������';// (�����������|�����������)

	//�������������
	public function __construct($paths){
		$this->paths=$paths;		
	}
	
	//������ XLS-�����
	public function ReadXLS($path){

		//������� ������
		$ext=substr($path,strrpos($path,'.')+1);
		if(IsSet($this->allowed_formats[$ext])){
			$function_name='ReadXLS_'.$this->allowed_formats[$ext];
			$res=$this->$function_name($path);
			return $res;

		//������ �� �������
		}else{
			return array('res'=>'������ ����� �� �������.','data'=>false);
		}
	}
	
	//������ XLS-����� ������� MS Office 2007
	private function ReadXLS_2007($path){
		set_include_path(get_include_path().PATH_SEPARATOR.$this->paths['libraries'].'/excel/Classes/');

		//include_once 'PHPExcel.php';
		include_once 'PHPExcel/RichText.php';
		include_once 'PHPExcel/Reader/Excel2007.php';

		$objReader = new PHPExcel_Reader_Excel2007();
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($path);

		//��������� ������
		if(!$objPHPExcel)return array('res'=>'������ ����� �� �������.','data'=>false);

		//�������� ������� ����
		$objPHPExcel_Worksheet=$objPHPExcel->getActiveSheet();

		$count_rows=$objPHPExcel_Worksheet->getHighestRow();
		$count_cols=$objPHPExcel_Worksheet->getHighestColumn();

		$p=$objPHPExcel_Worksheet->getCellCollection();

		$a=array();
		for($i=0;$i<=$count_rows;$i++){
			for($j='A',$k=0;$j<=$count_cols;$j++,$k++){
				$v=$objPHPExcel_Worksheet->getCell($j.$i)->getValue();//getCellByColumnAndRow($j,$i)->getValue();
				if(strlen($v)>0)
					$a[$i][$k+1]=@mb_convert_encoding($v,"Windows-1251","UTF-8");
			}
		}
		$data=array();
		$data[0]=$a;

		$res=array('res'=>'������ ������� ���������.','format'=>'2007','pages'=>1,'cols'=>$k,'rows'=>$count_rows,'data'=>$data);
		return $res;
	}

	//������ XLS-����� ������� MS Office 2003
	private function ReadXLS_2000($path){
		global $acms_CoreDocumentRoot;

		include_once($this->paths['libraries'].'/excel/Spreadsheet/Excel/reader.php');
		$prsr = new Spreadsheet_Excel_Reader();
		$prsr->setOutputEncoding('CP1251');
		$prsr->read($path);

		foreach($prsr->sheets as $s=>$sheet)if(IsSet($sheet['cells'])){
			foreach($sheet['cells'] as $i=>$row){
				foreach($row as $j=>$cell){
					$data[$s][$i][$j]=$cell;
				}
			}
		}
		$res=array('res'=>'������ ������� ���������.','format'=>'97-2003','pages'=>1,'cols'=>count($data[0][1]),'rows'=>count($data[0]),'data'=>$data);
		return $res;
	}
}

/*
����������: ����� ����.
Email: mishinoleg@mail.ru
Web: http://www.mishinoleg.ru/
*/

?>
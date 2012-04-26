<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		����� ������ � �������� ZIP							*/
/*															*/
/*	������ ���� 2.01										*/
/*	������ ������� 1.00										*/
/*															*/
/*	Copyright (c) 2009  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 17 ������ 2009	����							*/
/*	�������������: 17 ������ 2009 ����						*/
/*															*/
/************************************************************/

class zipper{
	
	public function __construct($config){
		$this->config=$config;
	}
	
	//������������� ����� � �����
	public function unzip( 
			$archive_path, 
			$to_folder=false, 
			$extensions=array('jpg','jpeg','gif','png') 
		){
		
		//���� ����� �� ������� - ��������� �� ��������� �����
		if(!$to_folder)$to_folder=$this->config['tmp'];

		//��������������� �����, ����������� �� ������
		$files=array();
	
		//�������� ������������� �����
		if(!is_dir($to_folder)){pr('����� ��� ���������� ������ �� �������. ['.$to_folder.']');exit();}
		//���������� ������ �� �����
		chmod($to_folder,0775);
		
		//��������� �����
		$f=zip_open($archive_path);
		//������ ����
		while($entry=zip_read($f)){
			//���������
			if(zip_entry_open($f,$entry,'r')){
				//������
				$buf=zip_entry_read($entry,zip_entry_filesize($entry));
				//��� �����
				$filename=basename(zip_entry_name($entry));
				//������ ���� � �����
				$finfo=pathinfo($filename);
				//����������
				$finfo['extension']=strtolower($finfo['extension']);
				//��������� ����������
				if( in_array($finfo['extension'],$extensions) ){
					//�������� ����� ���
					$fname=substr($filename, 0, strrpos($filename,'.') );
					//������ ����
					$filepath=$to_folder.'/'.$fname.'.'.$finfo['extension'];
					//��������
					$of=fopen($filepath,'w');
						fwrite($of,$buf);
						fclose($of);
					
					//���������� ������
					chmod($filepath,0775);
						
					//��������� ��������� ����������
					$files[]=array(
						'inzip_name'=>$filename,
						'filename'=>$fname.'.'.$finfo['extension'],
						'name'=>$fname,
						'extension'=>$finfo['extension'],
						'folder'=>$to_folder,
						'size'=>filesize($to_folder.'/'.$fname.'.'.$finfo['extension']),
					);
				}
				//��������� ����
				zip_entry_close($entry);
			}
		}
		//��������� �����
		zip_close($f);
		
		//������
		return $files;
	}
	
}

?>
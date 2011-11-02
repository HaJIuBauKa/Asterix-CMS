<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		������ � ������										*/
/*															*/
/*	������ ���� 2.02										*/
/*	������ ������� 1.00										*/
/*															*/
/*	Copyright (c) 2009  ����� ����							*/
/*	�����������: ����� ����									*/
/*	Email: dekmabot@gmail.com								*/
/*	WWW: http://mishinoleg.ru								*/
/*	������: 10 ������� 2009	����							*/
/*	�������������: 17 ������� 2010 ����						*/
/*															*/
/************************************************************/

class email{
	
	//��������� �� ���������
	var $from='cms@opendev.ru';
	private $encoding='koi8-r';
	
	//�������������� ���������
	private $supported_encodings=array('koi8-r','utf8');
	
	//�������� ���������
	public function send(
			$to,
			$subject,
			$message,
			$type='plain',
			$files=array()
		){

		//�������������� ������
		$address=$this->prepareAddress($to);
		$subject=$this->prepareSubject($subject,$type);
		$headers=$this->prepareHeaders($type,$files);
		$message=$this->prepareMessage($message,$subject,$type,$files);
		
		//��������
		foreach($address as $addr){
			mail($addr,$subject,$message,$headers);
		}
			
		//������
		return true;
	}
	
	
	public function __construct($model){
		$this->model = $model;
	}
	
	//����������� ����
	private function prepareSubject($subject,$type){
		
		//Plain
		if($type=='plain'){
			return '=?koi8-r?B?'.base64_encode( iconv( 'UTF-8', 'KOI8-R//IGNORE', stripslashes( $subject ) ) ).'?=';
		
		//HTML
		}elseif($type=='html'){
			return '=?koi8-r?B?'.base64_encode( iconv( 'UTF-8', 'KOI8-R//IGNORE', stripslashes( $subject ) ) ).'?=';
//			return iconv('utf-8', 'koi8-r//IGNORE', $subject);
		}
		
	}

	//����������� ���������
	private function prepareMessage($message,$subject,$type,$files){
		
		//Plain
		if($type=='plain'){
			$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
		
		//HTML
		}elseif($type=='html'){
			
			//����������� �����
			if(is_array($files))
				foreach($files as $file)$attachment .= $this->addAttachment($file);
		
			$message=iconv('utf-8', 'koi8-r//IGNORE', $message);
			$message='--'.md5(1).'
Content-Type: multipart/alternative; boundary="'.md5(2).'"

--'.md5(2).'
Content-Type: text/plain; charset="koi8-r"
Content-Transfer-Encoding: base64

'.base64_encode(strip_tags($message)).'
--'.md5(2).'
Content-Type: text/html; charset="koi8-r"
Content-Transfer-Encoding: base64

'.base64_encode('<html><head><title>'.$subject.'</title></head><body><p>'.$message.'</p></body></html>').'
--'.md5(2).'--

'.$attachment.'
--'.md5(1).'--
';
		}
		
		//������
		return $message;
	}

	//����������� ������ ��� ��������
	private function prepareAddress($to){
		
		//���� ���������
		$address=array();

		//��������� ����������
		if(substr_count($to,' '))
			$address=explode(' ',$to);
		//���� �����
		else
			$address[]=$to;
			
		//������
		return $address;
	}
	
	//����������� ���������
	private function prepareHeaders($type,$files){
		
		//Plain
		if($type=='plain'){
			$headers = 'from:'.$this->from;
		
		//HTML
		}elseif($type=='html'){
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'from: '.iconv('utf-8', 'koi8-r//IGNORE', $this->model->settings['domain_title']).' <'.$this->from . ">\r\n";
			$headers .= 'Content-Type: multipart/mixed; boundary="'.md5(1).'"' . "\r\n";
		}

		//������
		return $headers;
	}
	
	//���������� ���� � ���������
	function addAttachment($file){ 
		//��� �����
		$fname = substr(strrchr($file, "/"), 1); 
		//����������
		$data = file_get_contents($file); 
		$content = '--'.md5('1').'
--'.md5(1).'
Content-Type: text/plain; charset="windows-1251"; name="'.$fname.'"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="'.$fname.'"

'.chunk_split( base64_encode($data), 68, "\n").'
';
		return $content; 
	} 

	
	
	//����� ��������� �� ���������
	public function setEncoding($encoding='koi8-r'){
		//�������� � ������� �������
		$encoding=strtolower($encoding);
		//��������� ��������� ������ ���������
		if(in_array($encoding,$this->supported_encodings))
			//�������������
			$this->encoding=$encoding;
	}
	
}

?>
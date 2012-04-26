<?php

class acmsImageMask{

	//��������� ������ ��� ��������� ��������
	public function readMask( $mask_file_path ){

		//������ ��������
		$img = file_get_contents( $mask_file_path );
		$img = imagecreatefromstring( $img );
/*
		//��������� �������
		imagealphablending($img, false);
		//�������� �����-�����
		imagesavealpha($img, true);
*/
		//�������� ������ ������� � ���������� ������
		$transparent_index = imagecolortransparent( $img , 0x000000 );
		
		//������ �����
		$size = @getimagesize( $mask_file_path );
		$min_i = $size[0];
		$max_i = 0;
		$min_j = $size[1];
		$max_j = 0;
		$mask = array();
		for($i=0; $i<$size[0]; $i++)
			for($j=0; $j<$size[1]; $j++){
				
				//������ ����� � �������
				$index = imagecolorat($img, $i, $j);
				
				//���� � ������� ���������� ���� - ���������� ��� � �����
				if( $index == $transparent_index ){
					//����������
					$this -> mask[$i][$j] = true;
					
					//���������� ������� ���������� �����������
					$min_i = min($i, $min_i);
					$max_i = max($i, $max_i);
					$min_j = min($j, $min_j);
					$max_j = max($j, $max_j);
				}
			}
		
		//������� � �������� ������ �����
		$this->mask_width = $max_i - $min_i;
		$this->mask_height = $max_j - $min_j;
		$this->delta_x = $min_i;
		$this->delta_y = $min_j;
	}
	
	public function cutImage($file_path){
		if( !$this -> mask )
			return false;
		
		//������ ��������
		$src = file_get_contents( $file_path );
		$src = imagecreatefromstring( $src );

		$new = imagecreatetruecolor($this->mask_width, $this->mask_height);
		imagealphablending($new, false);
		imagesavealpha($new, true);

		$back = imagecolorallocatealpha($new, 0, 0, 0, 127);
		imagefilledrectangle($new, 0, 0, $this->mask_width, $this->mask_height, $back);

		for( $i=0; $i<$this->mask_width; $i++ )
			for( $j=0; $j<$this->mask_height; $j++ )
				if( $this->mask[$i + $this->delta_x][$j + $this->delta_y] ){
					
					//������ ����� � �������
					$index = imagecolorat($src, $i + $this->delta_x, $j + $this->delta_y);
					$rgb = imagecolorsforindex($src, $index);

					$color = imagecolorallocatealpha($new, $rgb['red'], $rgb['green'], $rgb['blue'], 0 ); 
					imagesetpixel($new, $i, $j, $color);
				}
		
		return $new;
	}
	
}

/*
$filter = new imageMaskFilter();

//�������� �����
$filter->readMask('mask.png');

//�������� ���� ���������� ������
$new = $filter->cutImage('src.jpg');

imagepng($new, 'tmp.png');
chmod('tmp.png', 0777);
print('<div style="background:url(src.jpg) left top; padding:100px; text-align: center;"><img src="data:image/png;base64,'. base64_encode( file_get_contents('tmp.png') ).'" alt="" /></div>');
*/

?>
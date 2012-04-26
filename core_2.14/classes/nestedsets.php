<?php

/************************************************************/
/*															*/
/*	���� ������� ���������� Asterix	CMS						*/
/*		��������� ������ � ��������� Nested Sets			*/
/*															*/
/*	������ ���� 2.0											*/
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

class nested_sets
{
	public function __construct($model, $table){
		//����������� ����������� ���������� (����)
		$this->model  = $model;
		$this->table  = $table;
	}	
	
	//�������� ��� ������
	public function getFull($fields, $condition = false){
		return $this->getSub(false, $fields, $condition);
	}
	
	//�������� ��� ���������
	public function getSub($record_id = false, $fields, $condition = false, $cache = false){
		$fields[] = 'tree_level';
		
        //����, ������� �����������
		if (is_array($fields)) {
            $fields = '`'.implode('`, `', $fields).'`';
        } else {
            $fields = '*';
        }
		
		//������� �������
		if( !$condition )
			$where = '1';
		else
			$where = '('.implode(') and (', $condition['and']).')';
		
		//������ ������
		if($record_id){
			$root = $this->model->execSql('select `left_key`, `right_key` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow');
			$where .= ' and `left_key`>='.$root['left_key'].' and `right_key`<='.$root['right_key'];
		}

		//���������
		$recs = $this->model->execSql('select '.$fields.' from `'.$this->table.'` where '.$where.' order by `left_key`', 'getall');
//		pr($this->model->last_sql);
		
		//������
		return $recs;
	}
	
	//���������� ������ � ����� �������
	public function addChild($parent_id, $record, $conditions = false){
	
		//���� ��������, � �������� ���������
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id), 'getrow');
		
		//��������� ����� �������, ������ ����� ������� ������ (���������� �����)
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+2) where `left_key`>'.$root['right_key'],'update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+2) where `right_key`>='.$root['right_key'],'update');

		//����� ����� ������
		$record['left_key'] = '`left_key`='.intval($root['right_key']);
		$record['right_key'] = '`right_key`='.intval($root['right_key']+1);
		$record['tree_level'] = '`tree_level`='.intval($root['tree_level']+1);
		
		//��������� � ������ ����� ������
		$this->model->execSql('insert into `'.$this->table.'` set '.implode(', ', $record).'','insert');
	}
	
	//����������� ������ ������������ ������
	public function moveChild($parent_id, $record_id, $condition = false){
		//�������� ������, ������� ����������
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow');
		
		//�������� ������ ��������, � ������� ���������� ������
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id), 'getrow');
		
		$right_key_near = $root['right_key']-1;
		
		//��������
		$skew_level = $root['tree_level'] - $record['tree_level'] + 1;
		$skew_tree = $record['right_key'] - $record['left_key'] + 1;
		$skew_edit = $right_key_near - $record['left_key'] + 1 - $skew_tree;
			
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'],'getall');
		$ids = array();
		foreach($t as $ti)$ids[]=$ti['id'];
		
		//���������� ����� �� ������
		if( $root['right_key']<$record['right_key']){
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.$skew_tree.') where `left_key`<'.$record['right_key'].' and `left_key`>'.$right_key_near,'update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+'.$skew_tree.') where `right_key`<='.$record['right_key'].' and `right_key`>'.$right_key_near,'update');

		//���������� ���� �� ������
		}else{
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$skew_tree.') where `left_key`>'.$record['right_key'].' and `left_key`<='.$right_key_near,'update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$skew_tree.') where `right_key`>'.$record['right_key'].' and `right_key`<='.$right_key_near,'update');
		}
		
		//���������� �����
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($skew_edit).'), `right_key`=(`right_key`+'.$skew_edit.'), `tree_level`=(`tree_level`+'.$skew_level.') where `id` IN ('.implode(',', $ids).')', 'update');
	}
	
	//�������� ������� ��� ������
	public function move($first_id, $second_id, $conditions = false){
		//�������� ������, ������� ����������
		$first = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id), 'getrow');
		
		//�������� ������ ��������, � ������� ���������� ������
		$second = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id), 'getrow');
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//��������
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
		$second_volume = $second['right_key'] - $second['left_key'] + 1;
			
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'],'getall');
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$second['left_key'].' and `right_key`<='.$second['right_key'],'getall');
		$second_ids = array();
		foreach($t as $ti)$second_ids[]=$ti['id'];
		
		//���������� �����
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update');
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update');
	}
	
	//�������� ������� ��� ������
	public function moveTo($first_id, $second_id, $conditions = false){
		//�������� ������, ������� ����������
		$first = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id), 'getrow');
		
		//�������� ������ ��������, � ������� ���������� ������
		$second = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id), 'getrow');
		
		//����������� �����������
		$move_down = $first['left_key'] < $second['left_key'];
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//��������
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
			
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'],'getall');
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID ������������ �������
		if( $move_down ){	//����
			$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>'.$first['right_key'].' and `right_key`<='.$second['right_key'],'getall');
			print('down: '.$this->model->last_sql.'<br />');
			$second_ids = array();
			foreach($t as $ti)$second_ids[]=$ti['id'];
			
			//��������
			$second_volume = $second['right_key'] - ($first['right_key']+1) + 1;

			//���������� �����
			if( count($first_ids) && count($second_ids) ){
			print('
update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')');
				$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update');
			print('
update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')');
				$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update');
			}
		
		}else{				//�����
			$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>'.$second['right_key'].' and `right_key`<'.$first['left_key'],'getall');
			print('up: '.$this->model->last_sql.'<br />');
			$second_ids = array();
			foreach($t as $ti)$second_ids[]=$ti['id'];
			
			//��������
			$second_volume = ($first['left_key']-1) - ($second['right_key']+1) + 1;

			//���������� �����
			if( count($first_ids) && count($second_ids) ){
			print('
update `'.$this->table.'` set `left_key`=(`left_key`-'.($second_volume).'), `right_key`=(`right_key`-'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')');
				$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($second_volume).'), `right_key`=(`right_key`-'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update');
			print('
update `'.$this->table.'` set `left_key`=(`left_key`+'.($first_volume).'), `right_key`=(`right_key`+'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')');
				$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($first_volume).'), `right_key`=(`right_key`+'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update');
			}
		}
	}
	
	//��������
	public function delete($record_id, $conditions = false){
		//�������� ������, ������� ����������
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow');
		
		//��������
		$volume = $record['right_key'] - $record['left_key'] + 1;

		//�������
		$this->model->execSql('delete from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'], 'delete');
		
		//����������� ���������
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$volume.') where `left_key`>'.$record['right_key'],'update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$volume.') where `right_key`>='.$record['right_key'],'update');
	}
	
}

?>
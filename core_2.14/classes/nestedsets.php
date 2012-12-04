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
			$root = model::execSql('select `left_key`, `right_key` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow', 'system', true);
			$where .= ' and `left_key`>='.$root['left_key'].' and `right_key`<='.$root['right_key'];
		}

		//���������
		$recs = model::execSql('select '.$fields.' from `'.$this->table.'` where '.$where.' order by `left_key`', 'getall', 'system', true);
//		pr(model::$last_sql);
		
		//������
		return $recs;
	}
	
	//���������� ������ � ����� �������
	public function addChild($parent_id, $record, $conditions = false){
	
		//���� ��������, � �������� ���������
		$root = model::execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id), 'getrow', 'system', true);
		
		//��������� ����� �������, ������ ����� ������� ������ (���������� �����)
		model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+2) where `left_key`>'.$root['right_key'],'update', 'system', true);
		model::execSql('update `'.$this->table.'` set `right_key`=(`right_key`+2) where `right_key`>='.$root['right_key'],'update', 'system', true);

		//����� ����� ������
		$record['left_key'] = '`left_key`='.intval($root['right_key']);
		$record['right_key'] = '`right_key`='.intval($root['right_key']+1);
		$record['tree_level'] = '`tree_level`='.intval($root['tree_level']+1);
		
		//��������� � ������ ����� ������
		model::execSql('insert into `'.$this->table.'` set '.implode(', ', $record).'','insert', 'system', true);
	}
	
	//����������� ������ ������������ ������
	public function moveChild($parent_id, $record_id, $condition = false){
		//�������� ������, ������� ����������
		$record = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow', 'system', true);
		
		//�������� ������ ��������, � ������� ���������� ������
		$root = model::execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id), 'getrow', 'system', true);
		
		$right_key_near = $root['right_key']-1;
		
		//��������
		$skew_level = $root['tree_level'] - $record['tree_level'] + 1;
		$skew_tree = $record['right_key'] - $record['left_key'] + 1;
		$skew_edit = $right_key_near - $record['left_key'] + 1 - $skew_tree;
			
		//ID ������������ �������
		$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'],'getall', 'system', true);
		$ids = array();
		foreach($t as $ti)$ids[]=$ti['id'];
		
		//���������� ����� �� ������
		if( $root['right_key']<$record['right_key']){
			model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.$skew_tree.') where `left_key`<'.$record['right_key'].' and `left_key`>'.$right_key_near,'update', 'system', true);
			model::execSql('update `'.$this->table.'` set `right_key`=(`right_key`+'.$skew_tree.') where `right_key`<='.$record['right_key'].' and `right_key`>'.$right_key_near,'update', 'system', true);

		//���������� ���� �� ������
		}else{
			model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$skew_tree.') where `left_key`>'.$record['right_key'].' and `left_key`<='.$right_key_near,'update', 'system', true);
			model::execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$skew_tree.') where `right_key`>'.$record['right_key'].' and `right_key`<='.$right_key_near,'update', 'system', true);
		}
		
		//���������� �����
		model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($skew_edit).'), `right_key`=(`right_key`+'.$skew_edit.'), `tree_level`=(`tree_level`+'.$skew_level.') where `id` IN ('.implode(',', $ids).')', 'update', 'system', true);
	}
	
	//�������� ������� ��� ������
	public function move($first_id, $second_id, $conditions = false){
		//�������� ������, ������� ����������
		$first = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id), 'getrow', 'system', true);
		
		//�������� ������ ��������, � ������� ���������� ������
		$second = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id), 'getrow', 'system', true);
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//��������
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
		$second_volume = $second['right_key'] - $second['left_key'] + 1;
			
		//ID ������������ �������
		$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'],'getall', 'system', true);
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID ������������ �������
		$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>='.$second['left_key'].' and `right_key`<='.$second['right_key'],'getall', 'system', true);
		$second_ids = array();
		foreach($t as $ti)$second_ids[]=$ti['id'];
		
		//���������� �����
		model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update', 'system', true);
		model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update', 'system', true);
	}
	
	//�������� ������� ��� ������
	public function moveTo($first_id, $second_id, $conditions = false){
		//�������� ������, ������� ����������
		$first = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id), 'getrow', 'system', true);
		
		//�������� ������ ��������, � ������� ���������� ������
		$second = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id), 'getrow', 'system', true);
		
		//����������� �����������
		$move_down = $first['left_key'] < $second['left_key'];
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//��������
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
			
		//ID ������������ �������
		$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'],'getall', 'system', true);
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID ������������ �������
		if( $move_down ){	//����
			$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>'.$first['right_key'].' and `right_key`<='.$second['right_key'],'getall', 'system', true);
			print('down: '.model::$last_sql.'<br />');
			$second_ids = array();
			foreach($t as $ti)$second_ids[]=$ti['id'];
			
			//��������
			$second_volume = $second['right_key'] - ($first['right_key']+1) + 1;

			//���������� �����
			if( count($first_ids) && count($second_ids) ){
			print('
update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')');
				model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update', 'system', true);
			print('
update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')');
				model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update', 'system', true);
			}
		
		}else{				//�����
			$t = model::execSql('select `id` from `'.$this->table.'` where `left_key`>'.$second['right_key'].' and `right_key`<'.$first['left_key'],'getall', 'system', true);
			print('up: '.model::$last_sql.'<br />');
			$second_ids = array();
			foreach($t as $ti)$second_ids[]=$ti['id'];
			
			//��������
			$second_volume = ($first['left_key']-1) - ($second['right_key']+1) + 1;

			//���������� �����
			if( count($first_ids) && count($second_ids) ){
			print('
update `'.$this->table.'` set `left_key`=(`left_key`-'.($second_volume).'), `right_key`=(`right_key`-'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')');
				model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($second_volume).'), `right_key`=(`right_key`-'.$second_volume.') where `id` IN ('.implode(',', $first_ids).')', 'update', 'system', true);
			print('
update `'.$this->table.'` set `left_key`=(`left_key`+'.($first_volume).'), `right_key`=(`right_key`+'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')');
				model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($first_volume).'), `right_key`=(`right_key`+'.$first_volume.') where `id` IN ('.implode(',', $second_ids).')', 'update', 'system', true);
			}
		}
	}
	
	//��������
	public function delete($record_id, $conditions = false){
		//�������� ������, ������� ����������
		$record = model::execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id), 'getrow', 'system', true);
		
		//��������
		$volume = $record['right_key'] - $record['left_key'] + 1;

		//�������
		model::execSql('delete from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'], 'delete', 'system', true);
		
		//����������� ���������
		model::execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$volume.') where `left_key`>'.$record['right_key'],'update', 'system', true);
		model::execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$volume.') where `right_key`>='.$record['right_key'],'update', 'system', true);
	}
	
}

?>
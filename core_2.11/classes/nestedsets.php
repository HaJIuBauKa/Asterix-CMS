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
	public function __construct($model, $table)
	{
		//����������� ����������� ���������� (����)
		$this->model  = $model;
		$this->table  = $table;
	}
	
	
	//�������� ��� ������
	public function getFull($fields, $condition = false)
	{
		return $this->getSub(false, $fields, $condition);
	}
	
	//�������� ��� ���������
	public function getSub($record_id = false, $fields, $condition = false, $cache = false)
	{
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
			$root = $this->model->execSql('select `left_key`, `right_key` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
			$where .= ' and `left_key`>='.$root['left_key'].' and `right_key`<='.$root['right_key'];
		}

		//���������
		$recs = $this->model->execSql('select '.$fields.' from `'.$this->table.'` where '.$where.' order by `left_key`', 'getall');
//		pr($this->model->last_sql);
		
		//������
		return $recs;
	}
	
	
	//���������� ������ � ����� �������
	public function addChild($parent_id, $record, $conditions = false)
	{
		//���� ��������, � �������� ���������
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//��������� ����� �������, ������ ����� ������� ������ (���������� �����)
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+2) where `left_key`>'.$root['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+2) where `right_key`>='.$root['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');

		//����� ����� ������
		$record['left_key'] = $root['right_key'];
		$record['right_key'] = $root['right_key']+1;
		$record['tree_level'] = $root['tree_level']+1;
		
		//��������� � ������ ����� ������
		$what = array();
		foreach($record as $var=>$val)
			$what[] = '`'.mysql_real_escape_string($var).'`="'.mysql_real_escape_string($val).'"';
		$this->model->execSql('insert into `'.$this->table.'` set '.implode(', ', $what).'','insert');
	}
	
	//����������� ������ ������������ ������
	public function moveChild($parent_id, $record_id, $condition = false)
	{
		//�������� ������, ������� ����������
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//�������� ������ ��������, � ������� ���������� ������
		$root = $this->model->execSql('select `right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($parent_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		$right_key_near = $root['right_key']-1;
		
		//��������
		$skew_level = $root['tree_level'] - $record['tree_level'] + 1;
		$skew_tree = $record['right_key'] - $record['left_key'] + 1;
		$skew_edit = $right_key_near - $record['left_key'] + 1 - $skew_tree;
			
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$ids = array();
		foreach($t as $ti)$ids[]=$ti['id'];
		
		//���������� ����� �� ������
		if( $root['right_key']<$record['right_key']){
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.$skew_tree.') where `left_key`<'.$record['right_key'].' and `left_key`>'.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`+'.$skew_tree.') where `right_key`<='.$record['right_key'].' and `right_key`>'.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');

		//���������� ���� �� ������
		}else{
			$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$skew_tree.') where `left_key`>'.$record['right_key'].' and `left_key`<='.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
			$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$skew_tree.') where `right_key`>'.$record['right_key'].' and `right_key`<='.$right_key_near.' and '.$this->model->extensions['domains']->getWhere().'','update');
		}
		
		//���������� �����
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($skew_edit).'), `right_key`=(`right_key`+'.$skew_edit.'), `tree_level`=(`tree_level`+'.$skew_level.') where `id` IN ('.implode(',', $ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
	}
	
	
	//�������� ������� ��� ������
	public function move($first_id, $second_id, $conditions = false)
	{
		//�������� ������, ������� ����������
		$first = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($first_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//�������� ������ ��������, � ������� ���������� ������
		$second = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($second_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		$right_key_near = $first['right_key'];
		$left_key_near = $first['left_key'];
		
		//��������
		$first_volume = $first['right_key'] - $first['left_key'] + 1;
		$second_volume = $second['right_key'] - $second['left_key'] + 1;
			
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$first['left_key'].' and `right_key`<='.$first['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$first_ids = array();
		foreach($t as $ti)$first_ids[]=$ti['id'];
		
		//ID ������������ �������
		$t = $this->model->execSql('select `id` from `'.$this->table.'` where `left_key`>='.$second['left_key'].' and `right_key`<='.$second['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','getall');
		$second_ids = array();
		foreach($t as $ti)$second_ids[]=$ti['id'];
		
		//���������� �����
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`+'.($second_volume).'), `right_key`=(`right_key`+'.$second_volume.') where `id` IN ('.implode(',', $first_ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.($first_volume).'), `right_key`=(`right_key`-'.$first_volume.') where `id` IN ('.implode(',', $second_ids).') and '.$this->model->extensions['domains']->getWhere().'', 'update');
	}
	
	//��������
	public function delete($record_id, $conditions = false)
	{
		//�������� ������, ������� ����������
		$record = $this->model->execSql('select `left_key`,`right_key`,`tree_level` from `'.$this->table.'` where `id`='.intval($record_id).' and '.$this->model->extensions['domains']->getWhere().'', 'getrow');
		
		//��������
		$volume = $record['right_key'] - $record['left_key'] + 1;

		//�������
		$this->model->execSql('delete from `'.$this->table.'` where `left_key`>='.$record['left_key'].' and `right_key`<='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'', 'delete');
		
		//����������� ���������
		$this->model->execSql('update `'.$this->table.'` set `left_key`=(`left_key`-'.$volume.') where `left_key`>'.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
		$this->model->execSql('update `'.$this->table.'` set `right_key`=(`right_key`-'.$volume.') where `right_key`>='.$record['right_key'].' and '.$this->model->extensions['domains']->getWhere().'','update');
	}
	
}

?>
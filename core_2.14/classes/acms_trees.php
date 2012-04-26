<?php

class acms_trees{

	//�������� ������� ������ ������
	public function getModuleShirtTree(
			$root_record_id,		//id ������, � ������� �������� ������� ������
			$structure_sid,			//������������ ��� ���������
			$levels_to_show,		//���������� �������, ������� ���������� �����
			$conditions=array()	//�c����� ������� �����
		){

		return acms_trees::getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
	}

	//�������� ������� ������ ��������
	public function getStructureShirtTree($root_record_id,$structure_sid,$levels_to_show,$conditions){
		//��������� ��������� ���������� �� ��������
		if(!$this->structure[$structure_sid]['hide_in_tree']){
			//����������� ���������
			if($this->structure[$structure_sid]['type']=='tree'){
				$recs = acms_trees::getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions);
			//�������� ���������
			}else{
				$recs = acms_trees::getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,false,$conditions);
			}
		}

		return $recs;
	}

	//����� �������� ������ � ����������� ���������
	public function getStructureShirtTree_typeTree($root_record_id,$structure_sid,$levels_to_show,$conditions){

		//���� �� ���������� ���������� ������� �������� - �������������
		if(!IsSet($this->structure[$structure_sid]['db_manager'])){
			require_once(model::$config['path']['core'].'/classes/nestedsets.php');
			$this->structure[$structure_sid]['db_manager']=new nested_sets($this->model,$this->getCurrentTable($structure_sid));
		}

		//��������� ������������ - �������� � Where ����������� �� ����������
		if($this->model->extensions)foreach($this->model->extensions as $ext){
			if( method_exists ( $ext , 'onSql' ) )
				list($a,$a,$where,$a,$a,$a)=$ext->onSql(false,false,$where,false,false,false);
		}

		//��������� ���������� � ������� �������
		if(is_array($conditions['and'])){
			if($where)
				$where['and']=array_merge($where['and'],$conditions['and']);
			else
				$where['and']=$conditions['and'];
		}

		//��������� �������
		if($levels_to_show > 0){
			//���� ������ ������ ������ ����� ������ - ����� ����� ���������� ������� ������������ ����������
			if($root_record_id){
				$rec=$this->getRecordById($structure_sid,$root_record_id);
				if($rec['tree_level']==1){
					$where['and']['tree_level']='( (`tree_level`>='.intval($rec['tree_level']).') and (`tree_level`<'.($rec['tree_level']+$levels_to_show).') )';
				}else{
					$where['and']['tree_level']='( (`tree_level`>'.intval($rec['tree_level']).') and (`tree_level`<='.($rec['tree_level']+$levels_to_show).') )';
				}
			}else{
				$where['and']['tree_level']='`tree_level`<='.$levels_to_show.'';
			}
		}
		
		//���� ������� ������ ������� ������ - ������� ���������
		if($root_record_id){

			//�������� �����
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//���� ��� ������
			$what = $this->getMainFields($structure_sid);
						
			//�������� ������ ������� ������
			$recs=$this->structure[$structure_sid]['db_manager']->getSub($root_record_id, $what, $where);
			
			//������� ������
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//����������
			log::sql('nested_sets -> getSub',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');

		//����� �������� ������ ������ ���������
		}else{
			//�������� �����
			$t=explode(' ',microtime());
			$sql_start=$t[1]+$t[0];

			//���� ��� ������
			$what = $this->getMainFields($structure_sid);

			//����� �������� ������ ������� ������������� �������� � ������������ �� ���������
			//������� ����� �������� ������ 2 ������ "tree_level", ��������������� ��
			if(count($this->structure)>1)
				if(IsSet($where['and']['tree_level']))
					$where['and']['tree_level']='`tree_level`>1';

			//�������� ������ ������� ������
			$recs=$this->structure[$structure_sid]['db_manager']->getFull($what,$where);

			//������� ������
			$t=explode(' ',microtime());
			$sql_stop=$t[1]+$t[0];
			$time=$sql_stop-$sql_start;

			//����������
			log::sql('nested_sets -> getFull',$time,$recs,$this->info['sid'],'getStructureShirtTree_typeTree');
		}

		if(!count($recs)){
//			pr('not found');
			if( (model::$ask->structure_sid != 'rec') ){
				// ������� ������� ��������� ���������
				// ����� � ��� ����� �������� ��������
				$search_children=false;
				foreach($this->structure as $s_sid=>$s)
					if($s['dep_path']['structure']==model::$ask->structure_sid){
						$search_children=$s_sid;
						$link_type = $s['dep_path']['link_type'];
					}

				if($search_children){
					//����� ������������ �� ����
					$dep_field_sid = model::$types[$link_type]->link_field;
					
					//���� ������ �������� ���������
					if( $rec[$dep_field_sid] ){
						$where = array( 
							'and' => array(
								'dep_path_'.model::$ask->structure_sid.''=>'`dep_path_'.model::$ask->structure_sid.'`="'.mysql_real_escape_string($rec[ $dep_field_sid ]).'"'
							)
						);
						$recs = acms_trees::getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
					}
					
				}
			}
		}
		
		//���� ������ �� ������ � ������� �� �������
		if($recs)
		foreach($recs as $i=>$rec){

			//��������� ������
			if($levels_to_show>2)
			if(strlen($rec['is_link_to_module'])){

				$recs[$i]['module']=$this->info['sid'];
				$recs[$i]['structure_sid']=$structure_sid;

				if(IsSet(model::$modules[$rec['is_link_to_module']])){
					if(is_object(model::$modules[$rec['is_link_to_module']])){

						//�������� ��������� ���������� ������
						$tree = model::$modules[$rec['is_link_to_module']]->getLevels('rec');
						$dep_structure_sid = $tree[count($tree)-1];
					
						//���� ������ ��������� ������
						$tmp=model::$modules[$rec['is_link_to_module']]->getModuleShirtTree(false,$dep_structure_sid,$levels_to_show-2,$conditions);
						//����� ��������� ������
						if(count($tmp)){

							//���� ��������� ������ ���� �� ���� � ���������� �������� - ���������
							if(IsSet($recs[$i]['sub'])){
								$recs[$i]['sub']=array_merge($recs[$i]['sub'],$tmp);
							//����������� ��������� ������, ������ ��������� ������
							}else{
								$recs[$i]['sub']=$tmp;
							}
						}
					}
				}
			}

			//��������� ��������� � �������� ����� ������
			if(count($this->structure)>1){
				//���� ��������� �������
				$levels=$this->getLevels('rec', array());
				$levels=array_reverse($levels);
				$next_structure_sid=false;
				foreach($levels as $j=>$level)if($level==$structure_sid)$next_structure_sid=@$levels[$j+1];
				//����� ��������� ��������� � ������ ������
				if($next_structure_sid){
					//�������� ����-������ � ������� ����������
					$field_name='dep_path_'.$structure_sid;
					//��������� ������� ������
					$where=$conditions;
					$where['and'][$field_name]='`'.mysql_real_escape_string($field_name).'`="'.mysql_real_escape_string($rec['sid']).'"';
				}
				//�������� ��������� ������ ���������
				$subs=acms_trees::getStructureShirtTree(false,$next_structure_sid,$levels_to_show-1,$where);
				//����� ��������� ������
				if($subs){
					//���� ��������� ������ ���� �� ���� � ���������� �������� - ���������
					if(IsSet($recs[$i]['sub'])){
						$recs[$i]['sub']=array_merge($recs[$i]['sub'],$subs);
					//����������� ��������� ������, ������ ��������� ������
					}else{
						$recs[$i]['sub']=$subs;
					}
				}
			}

		}

		//�������������� �� ��������� ������� �� ��������� ������
		$recs=self::reformRecords($recs,$recs[0]['tree_level'],0,count($recs));
		//��������� ��������� .html
		$recs=$this->insertRecordUrlType($recs);

		//������ ����� ������ �� ������ ������
		foreach($recs as &$rec){
			if(!IsSet($rec['module'])){
				$rec['module']=$this->info['sid'];
				$rec['structure_sid']=$structure_sid;
			}
		}

		return $recs;
	}

	//����� �������� ������ � �������� ���������
	public function getStructureShirtTree_typeSimple($root_record_id,$structure_sid,$levels_to_show,$where=false,$conditions=false){

		if($root_record_id){
//			pr('-> '.$this->info['sid'].'_'.$structure_sid.' ['.$root_record_id.']');

			// ������� ������� ��������� ���������
			// ����� � ��� ����� �������� ��������
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//������� ���������-�������
			if($search_children){
				$parent=$this->getRecordById($structure_sid,$root_record_id);

				//� ������ ����� ������������ ������ ���� ��� ������
				//���� ������ ���� ������
				$link_field=model::$types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//������� ����� ���������
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$parent[$link_field].'"');

				//��������� ���������� � ������� �������
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}

				//���� ��������
				if($search_children){
					$recs=acms_trees::getStructureShirtTree_typeSimple(false,$search_children,$levels_to_show,$where);
				}
			}

		//������� ��� ���������
		}else{

			//��������� ���������� � ������� �������
			if(is_array($conditions['and']) && is_array($where) ){
				$where['and']=array_merge($where['and'],$conditions['and']);
			}elseif(is_array($conditions['and'])){
				$where=$conditions;
			}

			// ������� ������� ��������� ���������
			// ����� � ��� ����� �������� ��������
			$search_children=false;
			if($structure_sid!='rec')
				if($this->structure)
					foreach($this->structure as $s_sid=>$s)
						if($s['dep_path']['structure']==$structure_sid){
							$search_children=$s_sid;
						}

			//����������:
			//���� ���� ���� POS - ��������� �� ����,
			//����� ��������� �� ��������� ����, � �������� �������
			$order=IsSet($this->structure[$structure_sid]['fields']['pos'])?'order by `pos`':'order by `date_public` desc';

			//�������� ������
			if($levels_to_show > 0){
				$recs=$this->model->makeSql(
					array(
						'tables'=>array($this->getCurrentTable($structure_sid)),
						'where'=>$where,
						'order'=>$order
					),
					'getall'
				);
			}//pr($this->model->last_sql);
			
			//��������� ����c���� ������ ���� �����
			if(is_array($recs))
			if($search_children)
			if($structure_sid!='rec')
			if($levels_to_show > 1)
			foreach($recs as $i=>$rec){

				//� ������ ����� ������������ ������ ���� ��� ������
				//���� ������ ���� ������
				$link_field=model::$types[$this->structure[$search_children]['dep_path']['link_type']]->link_field;

				//������� ����� ���������
				$where['and']=array('`dep_path_'.$structure_sid.'`="'.$rec[$link_field].'"');

				//��������� ���������� � ������� �������
				if(is_array($conditions['and'])){
					$where['and']=array_merge($where['and'],$conditions['and']);
				}

				//���� ��������
				if($search_children){
					$children=acms_trees::getStructureShirtTree_typeSimple($root_record_id,$search_children,$levels_to_show-1,$where);
					if($children)$recs[$i]['sub']=$children;
				}
			}
		}
		//��������� ��������� .html
		$recs=$this->insertRecordUrlType($recs);
		
		//������ ����� ������ �� ������ ������
		if($recs)
		foreach($recs as $i=>$rec){
			if(!IsSet($recs[$i]['module'])){
				$recs[$i]['module']=$this->info['sid'];
				$recs[$i]['structure_sid']=$structure_sid;
			}
		}

		//������
		if(count($recs))
			return $recs;
		else 
			return false;
	}
	
	//����������� ������� ���������������� ��������� ������ ������� � ������
	public static function reformRecords($recs,$level,$from,$to){
		$found=array();
		for($i=$from;$i<$to;$i++){
			if($recs[$i]['tree_level']==$level){
				$found[]=array('id'=>$i,'from'=>$i+1);
			}
		}
		$res=array();
		foreach($found as $i=>$f){
			if($i+1==count($found)){
				$new_subs=self::reformRecords($recs,$level+1,$f['from'],$to);
			}elseif($f['from']<$found[$i+1]['from']){
				$new_subs=self::reformRecords($recs,$level+1,$f['from'],$found[$i+1]['from']);
			}
			if($new_subs){
				//��� ���� �����-�� ����������
				if(is_array($recs[$f['id']]['sub']))
					$recs[$f['id']]['sub']=array_merge($new_subs,$recs[$f['id']]['sub']);
				//����������� ���� ���
				else
					$recs[$f['id']]['sub']=$new_subs;
			}
			$res[]=$recs[$f['id']];
		}
		return $res;
	}


}

?>
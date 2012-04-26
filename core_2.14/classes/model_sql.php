<?php

class ModelSql{

	//��������� ������� ������ � ���� ������
	public function execSql($sql, //������� sql-������
		$query_type = 'getall', //��������: getraw, getall, insert, update, delete
		$database = 'system', //������ ���� ������.
		$no_cache = false		//�� ������������ ��� �������
		){
		//�������� ����� ���������� �������
		$t         = explode(' ', microtime());
		$sql_start = $t[1] + $t[0];
/*
		if( $no_cache )
			pr('NO_CACHE: '.$sql.'|'.$query_type);
*/			
		//������������ ����������� - ����������� ���
        $result = false;

        if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
            $result = $this->cache->load( $sql.'|'.$query_type, model::$config['cache']['cache_timeout'] );
        }

        //���� ���� �� ������� - �������� ��� ������ ������
        if($result === false){

//			pr('sql:exec ['.$sql.'|'.$query_type.']');
		
            //��������� ����� ������
            if ($query_type == 'getrow') {
                $result = model::$db[$database]->GetRow($sql);

                //��������� ������ ������
            } elseif ($query_type == 'getall') {
                $result = model::$db[$database]->GetAll($sql);

                //���������� ������
            } elseif ($query_type == 'update') {
                //			pr($sql);
                if (model::$config['settings']['demo_mode']) {
                    print('� ������ ������������ �� �� ������ ������� ��������� � ���� ������. ������� "�����"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }

                //������� ������
            } elseif ($query_type == 'insert') {
                //			pr($sql);
                if ($this->config['settings']['demo_mode']) {
                    print('� ������ ������������ �� �� ������ ������� ��������� � ���� ������. ������� "�����"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }

                //�������� ������
            } elseif ($query_type == 'delete') {
                if ($this->config['settings']['demo_mode']) {
                    print('� ������ ������������ �� �� ������ ������� ��������� � ���� ������. ������� "�����"');
                    exit();
                } else {
                    $result = model::$db[$database]->Execute($sql);
                }
            }

            //������������ ����������� - ���������� ���������
            if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
                $this->cache->save( $result, $sql.'|'.$query_type );
            }
        }else{
//			pr('sql:cache');
		}

		//������� ������
		$t        = explode(' ', microtime());
		$sql_stop = $t[1] + $t[0];
		$time     = $sql_stop - $sql_start;

		//����������
		log::sql($sql, $time, $result, $query_type, $database);

		//���������� ��������� ������
		model::$last_sql = $sql;

		//������
		return $result;
	}

	//����������� ������ � ���� ������ �� ������ ��������������� �������������
	public function makeSql(
		$sql_conditions = array('fields' => array(), 'tables' => array(), 'where' => array(), 'group' => array(), 'order' => '', 'limit' => false), //������ ������� ��� sql-�������
		$query_type = 'getall', //��������: getrow, getall, insert, update, delete
		$database = 'system', 	//������ ���� ������.
		$no_cache = false		//�� ������������ ��� �������
		){
		
		if (model::$extensions)
			foreach (model::$extensions as $ext)
				if( method_exists ( $ext , 'onSql' ) )
					list($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit']) = $ext->onSql($sql_conditions['fields'], $sql_conditions['tables'], $sql_conditions['where'], $sql_conditions['group'], $sql_conditions['order'], $sql_conditions['limit'], $query_type);

		//��� �����������
		if ($query_type == 'getrow' or $query_type == 'getall') {
			
			//�� ������� ��� ����������� - �������� ��
			if( !is_array( $sql_conditions['fields'] ) ){
				$fields = '*';
			
			//������� ��� ��������� ��������			
			}else{
				//��������� � ���������
				$fields = '';
				foreach($sql_conditions['fields'] as $field){
					if($fields != '')$fields .= ', ';
					
					if(substr_count($field, ' as '))
						$fields .= $field;
					else
						$fields .= '`'.$field.'`';
						
				}
			}

		} elseif ($query_type == 'insert' or $query_type == 'update') {
			//��������� ��� �������
			$fields = implode(', ', $sql_conditions['fields']);
		}

		//�������
		if ($sql_conditions['where']) {
			if (is_array($sql_conditions['where'])) {
				$res = '';
				foreach ($sql_conditions['where'] as $logic => $vars) {
					$res_logic = '';
					if (is_array($vars))
						foreach ($vars as $i => $val)
							if (strlen($val)) {
								if (strlen($res_logic))
									$res_logic .= ' ' . $logic . ' ';
								$res_logic .= $val;
							}
					if (strlen($res))
						$res .= ' ' . $logic . ' ';
					$res .= '(' . $res_logic . ')';
				}
				$where = $res;
			}
		}

		//�������
		$tables = '`'.implode('`, `', $sql_conditions['tables']).'`';

		//�������
		$order = $sql_conditions['order'];

		//�����������
		$limit = $sql_conditions['limit'];

		//��������� ����� ������
		if ($query_type == 'getrow') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' limit 1';

			//��������� ������ ������
		} elseif ($query_type == 'getall') {
			$sql = 'select ' . $fields . ' from ' . $tables . '' . ($where ? ' where ' . $where : '') . ' ' . $group . ' ' . $order . ' ' . $limit . '';

			//���������� ������
		} elseif ($query_type == 'update') {
			$sql = 'update ' . $tables . ' set ' . $fields . ' where ' . $where . ' ' . $limit . '';

			//������� ������
		} elseif ($query_type == 'insert') {
			$sql = 'insert into ' . $tables . ' set ' . $fields . '';

			//�������� ������
		} elseif ($query_type == 'delete') {
			$sql = 'delete from ' . $tables . ' where ' . $where . '';
		}

		//����� ������������
		if (model::$config['settings']['demo_mode'] and (in_array($query_type, array(
			'update',
			'insert',
			'delete'
		)))) {
			print('� ������ ������������ �� �� ������ ������� ��������� � ���� ������. ������� "�����"');
			exit();
		} else {
			
			//��������� ������
			$result = model::execSql($sql, //������� sql-������
				$query_type, 	//��������: getraw, getall, insert, update, delete
				$database, 		//������ ���� ������.
				$no_cache		//�� ������������ ��� �������
			);
		}

		return $result;
	}


}

?>
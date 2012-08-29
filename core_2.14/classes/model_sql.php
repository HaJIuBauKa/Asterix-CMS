<?php

class ModelSql{

	//��������� ������� ������ � ���� ������
	public static function execSql($sql, //������� sql-������
		$query_type = 'getall', //��������: getraw, getall, insert, update, delete
		$database = 'system', //������ ���� ������.
		$no_cache = false		//�� ������������ ��� �������
		){
		
		if( !strlen( $sql ) )
			return false;

/*
		// ������ �� ���� ������
		if( !$no_cache ){
			$result = cache::readSqlCache( $sql );
			if( $result )
				return $result;
		}
*/	
	
		//�������� ����� ���������� �������
		$t         = explode(' ', microtime());
		$sql_start = $t[1] + $t[0];

		//������������ ����������� - ����������� ���
        $result = false;

        if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
            $result = model::$cache->load( $sql.'|'.$query_type, model::$config['cache']['cache_timeout'] );
        }

		$result_count = 0;
		
        //���� ���� �� ������� - �������� ��� ������ ������
        if($result === false){

			// ��������� ����� ������
            if ($query_type == 'getrow') {
                $result = model::$db[$database]->GetRow($sql);
				$result_count = 1;

			// ��������� ������ ������
            } elseif ($query_type == 'getall') {
                $result = model::$db[$database]->GetAll($sql);
				$result_count = count( $result );

			// ������� ������
            } elseif ( in_array($query_type, array('insert', 'replace', 'update', 'delete') ) ) {
				model::check_demo();
				$result = model::$db[$database]->Execute($sql);
				$result_count = 0;
			}

            //������������ ����������� - ���������� ���������
            if( model::$config['cache'] and (!$no_cache) and in_array($query_type, array('getrow', 'getall') ) ){
                model::$cache->save( $result, $sql.'|'.$query_type );
            }
		}

		//������� ������
		$t        = explode(' ', microtime());
		$sql_stop = $t[1] + $t[0];
		$time     = $sql_stop - $sql_start;

		//����������
		log::sql($sql, $time, $result_count, $query_type, $database);

		//���������� ��������� ������
		model::$last_sql = $sql;
		
/*
		// ���������� ��������� � ���� ������
		cache::makeSqlCache( $sql, $result );
*/		

		//������
		return $result;
	}

	//����������� ������ � ���� ������ �� ������ ��������������� �������������
	public static function makeSql(
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

		} elseif ( in_array($query_type, array('insert','update','replace') ) ) {
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

		//������� ������
		} elseif ($query_type == 'replace') {
			$sql = 'replace into ' . $tables . ' set ' . $fields . '';

		//�������� ������
		} elseif ($query_type == 'delete') {
			$sql = 'delete from ' . $tables . ' where ' . $where . '';
		}

		//����� ������������
		if (model::$config['settings']['demo_mode'] and (in_array($query_type, array(
			'update',
			'insert',
			'replace',
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
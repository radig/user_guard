<?php
App::import('CORE', 'ConnectionManager');

class AutoTrimBehavior extends ModelBehavior {
	
	private $_Model;
	
	/**
	 * Lista de formatos para os dados suportados pelo BD em uso.
	 * É recuperado automáticamente pela conexão com o banco.
	 *
	 * @var array
	 */
	private $_typesFormat;
	
	/**
	 * Lista com nomes dos modelos nos quais o behavior
	 * não fará conversão.
	 * 
	 * Isso permite a compatibilidade com datasources que
	 * não possuem tipos definidos.
	 * 
	 * @var array
	 */
	private $_disabledFor = array();
	
	/**
	 * Inicializa os dados do behavior
	 *
	 * @see ModelBehavior::setup()
	 */
	public function setup(&$model, $config = array())
	{
		$this->_Model =& $model;
		
		$db =& ConnectionManager::getDataSource($this->_Model->useDbConfig);
		
		if(!isset($db->columns) || empty($db->columns))
		{
			$this->_disabledFor[$model->name] = true;
		}
		else
		{
			foreach($db->columns as $type => $info)
			{
				if(isset($info['format']))
				{
					$this->_typesFormat[$type] = $info['format'];
				}
			}
		}
	}
	
	/**
	* Trim através do callback beforeValidate
	*
	* @see ModelBehavior::beforeValidate()
	*/
	public function beforeValidate(&$model)
	{
		$this->_Model =& $model;
	
		parent::beforeValidate($model);
		
		if(isset($this->_disabledFor[$model->name]))
		{
			return true;
		}
		
		return $this->_autoTrim();
	}
	
	/**
	 * Trim através do callback beforeSave
	 *
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(&$model)
	{
		$this->_Model =& $model;
	
		parent::beforeSave($model);
		
		if(isset($this->_disabledFor[$model->name]))
		{
			return true;
		}
	
		return $this->_autoTrim();
	}
	
	/**
	 * Trim das informações no callback beforeFind
	 *
	 * @see ModelBehavior::beforeFind()
	 */
	public function beforeFind(&$model, $query)
	{
		$this->_Model =& $model;
	
		parent::beforeFind($mode, $query);
		
		if(!isset($this->_disabledFor[$model->name]))
		{
			$this->_autoTrim($query['conditions']);
		}
	
		return $query;
	}
	
	private function _autoTrim(&$query = null)
	{
		// verifica se há dados setados no modelo
		if(isset($this->_Model->data[$this->_Model->name]) && !empty($this->_Model->data[$this->_Model->name]))
		{
			// varre os dados setados
			foreach($this->_Model->data[$this->_Model->name] as $field => $value)
			{
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if(!empty($value) && !is_array($value) && isset($this->_Model->_schema[$field]))
				{
					switch($this->_Model->_schema[$field]['type'])
					{
						case 'string':
						case 'text':
							$this->_Model->data[$this->_Model->name][$field] = trim($this->_Model->data[$this->_Model->name][$field]);
					}
				}
			}
		}
		
		// caso tenha sido invocado em um Find (haja query de busca)
		if(!empty($query) && is_array($query))
		{
			// varre os campos da condição
			foreach($query as $field => &$value)
			{
				if(strtolower($field) === 'or' || strtolower($field) === 'and' || is_numeric($field))
				{
					$this->_autoTrim($value);
					continue;
				}
			
				// caso sejam campos com a notação Model.field
				if(strpos($field, '.') !== false)
				{
					$ini = strpos($field, '.');
					$len = strpos($field, ' ');
						
					$modelName = substr($field, 0, $ini - 1);
						
					if($len !== false)
						$field = substr($field, $ini + 1, $len - $ini - 1);
					else
						$field = substr($field, $ini + 1);
				}
			
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if(!empty($value) && isset($this->_Model->_schema[$field]))
				{
					switch($this->_Model->_schema[$field]['type'])
					{
						case 'string':
						case 'text':
							$value = trim($value);
					}
				}
			}
		}
	}
}
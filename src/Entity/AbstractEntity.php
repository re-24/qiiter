<?php
namespace re24\Qiiter\Entity;

use re24\Qiiter\Exception\EntityException;

/**
 * Abstract Entity class
 */
abstract class AbstractEntity 
{

	/**
	 * 配列に複数のEntityを持っている要素のリストを返す。
	 * (プロパティに持つと…自動でプロパティ展開されちゃうのでメソッドにした)
	 * 
	 * @return array 
	 */
	private function getArrayEntity()
	{
		return ['tags' => 'tagging'];
	}
	
	/**
	 * @throws EntityException
	 */
	public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw EntityException::notContinProperty(get_class($this),$name);
        }

        return $this->{$name};
    }
	
	/**
	 * @throws EntityException
	 */
	public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw EntityException::notContinProperty(get_class($this),$name);
        }

		$this->{$name} = $value;

        return $this;
    }
	
	/**
	 * Entityを配列として返す
	 * 
	 * @return array 
	 */
	public function getArray()
	{
		$propeties = get_object_vars($this);
		$arr = [];
		foreach($propeties as $key=>$val) {
			if (is_null($val)) {
				continue;
			}if($val instanceof \re24\Qiiter\Entity\AbstractEntity) {
				$arr[$key] = $val->getArray();
			}elseif(is_array($val)){
				$entities = $val;
				$entity_array = [];
				
				foreach($entities as $entity) {
					if ( $entity instanceof \re24\Qiiter\Entity\AbstractEntity) {
						$entity_array[] = $entity->getArray();
					} else {
						$entity_array[] = $entity;
					}
				}
				$arr[$key] = $entity_array;

			}else {
				$arr[$key] = $val;
			}
		}
		
		return $arr;
	}
	
	/**
	 * 配列からEntityへのデータ変換
	 * 
	 * propetyとkeyが一致するもののみ処理を行う
	 * 
	 * @param array $data
	 * @return \re24\Qiiter\Entity\AbstractEntity
	 */
	public function arrayToEntity(array $data)
	{
		foreach($data as $key=>$val) {
			$key = strtolower($key);
			
			if(!property_exists($this,$key)) {
				continue;
			}

			$array_entity = $this->getArrayEntity();
			if(key_exists($key, $array_entity)) {
				$entitys = [];
				$obj_name = "re24\\Qiiter\Entity\\".ucfirst($array_entity[$key]);
				foreach($data[$key] as $value) {
					$obj = new $obj_name();
					$obj->arrayToEntity($value);
					$entitys[] = $obj;
				}
				$this->{$key} = $entitys;
			}elseif(is_array($val) && class_exists( "re24\\Qiiter\Entity\\".ucfirst($key))) {
				$obj_name = "re24\\Qiiter\Entity\\".ucfirst($key);
				$obj = new $obj_name();
				$obj->arrayToEntity($val);
				
				$this->{$key} = $obj;
			} else {
				$this->{$key} = $val;
			}

		}
		
		return $this;
	}
}
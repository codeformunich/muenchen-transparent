<?php

/**
 * This is the model class for table "rechtsdokument".
 *
 * The followings are the available columns in table 'texte':
 * @property integer $id
 * @property string $datum
 * @property string $url
 * @property integer $jahr
 * @property integer $nr
 *
 * @property Dokument[] $dokumente
 */
class Rathausumschau extends CActiveRecord  implements IRISItem
{

	/**
	 * @param string $className active record class name.
	 * @return Rechtsdokument the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'rathausumschau';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('url, datum, jahr, nr', 'required'),
			array('id, jahr, nr', 'numerical', 'integerOnly' => true),
			array('url', 'length', 'max' => 200),
			array('datum', 'length', 'max' => 10),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'dokumente' => array(self::HAS_MANY, 'Dokument', 'rathausumschau_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'    => 'ID',
			'datum' => 'Datum',
			'url'   => 'URL',
			'jahr'  => 'Jahr',
			'nr'    => 'Nr.',
		);
	}

	/**
	 * @param array $add_params
	 * @return string
	 */
	public function getLink($add_params = array())
	{
		if (count($this->dokumente) > 0) {
			return $this->dokumente[0]->getLink();
		} else {
			return "http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service.html";
		}
	}

	/** @return string */
	public function getTypName()
	{
		return "Rathausumschau";
	}

	/** @return string */
	public function getDate()
	{
		return $this->datum;
	}

	/**
	 * @param bool $kurzfassung
	 * @return string
	 */
	public function getName($kurzfassung = false)
	{
		if ($kurzfassung) return "Rathausumschau " . $this->nr . "/" . substr($this->datum, 0, 4);
		else return "Rathausumschau " . $this->nr . " (" . $this->datum . ")";
	}
}
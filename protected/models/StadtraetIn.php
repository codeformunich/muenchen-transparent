<?php

/**
 * @property integer $id
 * @property string $gewaehlt_am
 * @property string $bio
 * @property string $web
 * @property string $name
 * @property string $twitter
 * @property string $facebook
 * @property string $abgeordnetenwatch
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property Person[] $personen
 * @property StadtraetInFraktion[] $stadtraetInnenFraktionen
 */
class StadtraetIn extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StadtraetIn the static model class
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
		return 'stadtraetInnen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, name', 'required'),
			array('id', 'numerical', 'integerOnly' => true),
			array('web', 'length', 'max' => 250),
			array('name', 'length', 'max' => 100),
			array('twitter', 'length', 'max' => 45),
			array('facebook, abgeordnetenwatch', 'length', 'max' => 200),
			array('gewaehlt_am', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, gewaehlt_am, bio, web, name, twitter, facebook, abgeordnetenwatch', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'antraege'                 => array(self::MANY_MANY, 'Antrag', 'antraege_stadtraetInnen(stadtraetIn_id, antraege_id)'),
			'personen'                 => array(self::HAS_MANY, 'Person', 'ris_stadtraetIn'),
			'stadtraetInnenFraktionen' => array(self::HAS_MANY, 'StadtraetInFraktion', 'stadtraetIn_id', 'order' => 'wahlperiode DESC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                => 'ID',
			'gewaehlt_am'       => 'Gewaehlt Am',
			'bio'               => 'Bio',
			'web'               => 'Web',
			'name'              => 'Name',
			'twitter'           => 'Twitter',
			'facebook'          => 'Facebook',
			'abgeordnetenwatch' => 'Abgeordnetenwatch'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('gewaehlt_am', $this->gewaehlt_am, true);
		$criteria->compare('bio', $this->bio, true);
		$criteria->compare('web', $this->web, true);
		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return Yii::app()->createUrl("stadtraetIn/anzeigen", array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		return "Stadtratsmitglied";
	}

	/**
	 * @param bool $kurzfassung
	 * @return string
	 */
	public function getName($kurzfassung = false)
	{
		return $this->name;
	}

	/**
	 * @param string $datum
	 * @return array[]
	 */
	public static function getGroupedByFraktion($datum)
	{
		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("antraege_dokumente")->where("id < 1245865 AND (seiten_anzahl = 0 OR seiten_anzahl = 9)")->order("id");
		/** @var StadtraetIn[] $strs */
		$strs       = StadtraetIn::model()->findAll(array(
			'alias' => 'a',
			'order' => 'a.name ASC',
			'with'  => array(
				'stadtraetInnenFraktionen'          => array(
					'alias'     => 'b',
					'condition' => 'b.datum_von <= "' . addslashes($datum) . '" AND (b.datum_bis IS NULL OR b.datum_bis >= "' . addslashes($datum) . '")',
				),
				'stadtraetInnenFraktionen.fraktion' => array()
			)));
		$fraktionen = array();
		foreach ($strs as $str) {
			if (!isset($fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id])) $fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id] = array();
			$fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id][] = $str;
		}
		return $fraktionen;
	}
}
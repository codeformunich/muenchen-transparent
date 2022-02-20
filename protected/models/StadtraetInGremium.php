<?php

/**
 * @property integer $stadtraetIn_id
 * @property integer $gremium_id
 * @property string $datum_von
 * @property string|null $datum_bis
 * @property string $funktion
 *
 * The followings are the available model relations:
 * @property Gremium $gremium
 * @property StadtraetIn $stadtraetIn
 */
class StadtraetInGremium extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StadtraetInGremium the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'stadtraetInnen_gremien';
    }

    public function rules(): array
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['stadtraetIn_id, gremium_id, datum_von', 'required'],
            ['stadtraetIn_id, gremium_id', 'numerical', 'integerOnly' => true],
            ['datum_von, datum_bis', 'length', 'max' => 10],
            ['funktion, datum_von, datum_bis, created, modified', 'safe'],
        ];
    }

    public function relations(): array
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'gremium'     => [self::BELONGS_TO, 'Gremium', 'gremium_id'],
            'stadtraetIn' => [self::BELONGS_TO, 'StadtraetIn', 'stadtraetIn_id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'stadtraetIn_id' => 'StadträtIn',
            'gremium_id'     => 'Gremium',
            'funktion'       => 'Funktion',
            'datum_von'      => 'Von',
            'datum_bis'      => 'Bis',
        ];
    }

    public function mitgliedschaftAktiv(): bool {
        $date = str_replace("-", "", date("Y-m-d"));

        if (is_null($this->datum_bis)) return true;
        $bis = str_replace("-", "", $this->datum_bis);

        return ($bis >= $date);
    }

    public function getDatumVonTimestamp(): int
    {
        $date = DateTime::createFromFormat('Y-m-d', $this->datum_von);
        return $date->getTimestamp();
    }
}

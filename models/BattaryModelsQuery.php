<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[BattaryModels]].
 *
 * @see BattaryModels
 */
class BattaryModelsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BattaryModels[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BattaryModels|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

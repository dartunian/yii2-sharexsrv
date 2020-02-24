<?php

namespace app\models;

use yii\db\ActiveRecord;

class FileEntry extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{file-entry}}';
    }
}
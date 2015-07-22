<?php
/**
 * Created by David
 * User: David.Fang
 * Date: 2015/7/20
 * Time: 15:17
 */

namespace backend\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;

class AdminUser extends User {
    public $password;
    public $password_repeat;
    public $verifyCode;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username','unique'],
            [['username', 'password'], 'required'],
            [['password_repeat'],'required','on'=>['create','update','chgpwd']],
            [['oldpassword','password_repeat'],'required','on'=>['chgpwd','update']],
            //['verifyCode','captcha','on'=>['create','chgpwd']],//
            [['username', 'password', 'userphoto'], 'string', 'max' => 255],
            ['password_repeat','compare','compareAttribute'=>'password']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'oldpassword' => '原密码',
            'password' => '密码',
            'password_repeat'=>'重复密码',
            'verifyCode'=>'验证码',
            'email'=>'邮箱',
            'userphoto'=>'用户头像',
        ];
    }
    public function beforeSave($insert)
    {
        if($this->isNewRecord || $this->password_hash!=$this->oldAttributes['password'])
            $this->password_hash = \Yii::$app->security->generatePasswordHash($this->password_hash);
        return true;
    }
    public function getInfo(){
        return $this->hasOne(AdminInfo::className(), ['id' => 'id']);
    }
    /**
     * 关联获取角色
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(AuthAssignment::className(),['user_id'=>'id']);
    }
    public static function findByusername($username)
    {
        return static::find()->where('username=:u',[':u'=>$username])->one();
    }
}
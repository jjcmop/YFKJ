<?php
/**
 * Created by PhpStorm.
 * User: gly
 * Date: 2019/8/20
 * Time: 17:07
 */

/**
 * 用户模型
 *
 * 写法请参考 ThinkPHP 5.1的数据模型
 */

class UserModel extends Model
{
    public function getUserInfo($uid)
    {

        return $this->where('uid', $uid)->find();
    }

    /**
     * 新增用户s
     * @param $data
     */
    public function add($data)
    {
        return $this->save($data);
    }

}
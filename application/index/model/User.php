<?php
namespace app\index\Model;
use think;
use Think\Db;
use think\Model;


class User extends Model
{

    protected $table =   'givenchy_user'; //用户表
    protected $send_message_log_table =   'givenchy_send_message_log';//发送短信日志表
    protected $reserved_table =   'givenchy_reserved_mobile';//预留手机号表
    protected $delivery_table =   'givenchy_delivery_mobile';//收件人手机号表

    //根据open_id查询用户
    public  function  getUserInfoByOpenId($open_id){
        return Db::table($this->table)->where(['open_id'=>trim($open_id)])->find();
    }

    //根据uid查询用户
    public  function  getUserInfoById($id){
        return Db::table($this->table)->where(['id'=>(int)$id])->find();
    }

    //更改用户表信息
    public function UpdateUserInfo($mobile,$open_id){
        return Db::table($this->table)
            ->where(['open_id'=>$open_id])
            ->update(['mobile'=>$mobile]) ;
    }

    //获取用户预留的手机号
    public function getReserverMobileByUid($open_id){
        return Db::table($this->table)->field('mobile')->where(['open_id'=>$open_id])->find();
    }

    //查询预留的手机号
    public  function  selectReserverMobile($mobile){
         return Db::table($this->reserved_table)->where(['mobile'=>$mobile])->find();
    }

    //添加预留的手机号
    public  function  addReserverMobile($mobile){
       return  Db::table($this->reserved_table)->insert(['mobile'=>$mobile,'create_time'=>time()]);
    }


    //查找需要发送的手机号
    public function getSendMessageReserverMobile($where){
        return Db::table($this->reserved_table)->field('mobile')->where($where)->select();
    }

    //修改发送手机的状态
    public function updateSendMessageReserverStatus($mobile){
        return  Db::table($this->reserved_table)->where(['mobile'=>$mobile,'status'=>0])->update(['status' => 1]) ;
    }

    //短信日志
    public function sendMessageLog($mobile,$content,$send_type,$retun_msg,$status)
    {
        return Db::table($this->send_message_log_table)->insert([
            'mobile' => $mobile,
            'send_content' => $content,
            'send_type' => $send_type,//类型：1-开售前群发通知 2-发货后通知
            'retun_msg' => $retun_msg,
            'send_time' => time(),
            'status' => $status, //状态：1-成功 2-失败 3- 退订了
        ]);
    }
    //查找失败的短信日志
    public function sendMessageErrorData(){
        return Db::table($this->send_message_log_table)
            ->field('id,mobile,send_content,send_type')
            ->where(['status'=>2,'deal_status'=>0])
            ->select();
    }

    //更新发送短信日志
    public function UpdateSendMessageErrorData($id){
        return Db::table($this->send_message_log_table)
            ->where(['status'=>2,'deal_status'=>0,'id'=>$id])
            ->update(['deal_status'=>1]) ;
    }
    //删除发送短信日志
    public function DelSendMessageById($id){
        return Db::table($this->send_message_log_table)->where('id',$id)->delete();
    }




    //添加收货人的手机号
    public  function  addDeliveryMobile($mobile,$send_message){
//        if($this->selectDeliveryMobile($mobile)) return true; //存在
        return  Db::table($this->delivery_table)->insert(['mobile'=>$mobile,'send_content'=>$send_message]);
    }

    //查询预留的手机号
    public  function  selectDeliveryMobile($mobile){
        return Db::table($this->delivery_table)->where(['mobile'=>$mobile])->find();
    }

    //查找需要发送的手机号们
    public function getSendMessageDeliveryMobile($where){
        return Db::table($this->delivery_table)->field('mobile,send_content')->where($where)->select();
    }

    //修改发送收货人手机的状态
    public function updateSendMessageDeliveryStatus($mobile){
        return  Db::table($this->delivery_table)->where(['mobile'=>$mobile,'status'=>0])->update(['status' => 1]) ;
    }





}

?>
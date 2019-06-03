<?php
namespace app\index\Model;
use think;
use Think\Db;
use think\Model;


class Order extends Model
{
    protected $table =   'givenchy_order_info';//订单表
    protected $notify_table = 'givenchy_wxpay_notify';//微信回调log表
    protected $order_before_table =   'givenchy_user_order_before';//下单防并发表
    protected $inventory_table =   'givenchy_inventory';//库存表
    protected $auth_log_table =   'givenchy_auth_log';//授权日志表

    /**
     * 授权日志表
     * @param $url
     * @param int $type :0-发起请求  1-三方接口返回
     * @return int|string
     */
    public function addAuthLog($url,$type=0){
        $data = [];
        $data['url'] = $url;
        $data['type'] = (int)$type;
        $data['create_time'] = time();
        return Db::table($this->auth_log_table)->insert($data);
    }

   //微信支付回调信息
    public function addWxPayNotify($data){
        return Db::table($this->notify_table)->insert($data);
    }

    //成功的订单
    public  function  getSuccessOrderInfoByOpenid($open_id){
        if(!$open_id) return false;
        return Db::table($this->table)->field('order_id')->where("open_id='{$open_id}' AND status IN (1,2)")->find();
    }

    //查找用户未付款的订单
    public  function  getWaitOrderInfoByOpenid($open_id){
        return Db::table($this->table)->field('id,open_id,order_id,status')->where(['open_id'=>trim($open_id),'status'=>0])->find();
    }
    //创建订单
    public function addOrder($data){
        return Db::table($this->table)->insert($data);
    }


    //根据订单ID查找详情
    public  function  getOrderInfoByOrderId($order_id){
        return Db::table($this->table)->where(['order_id'=>trim($order_id)])->find();
    }

    //更新订单
    public function updateOrderPay($data,$order_id){
        return Db::table($this->table)->where(['order_id'=>$order_id])->update($data) ;
    }

    public function updateOrderPayId($data,$id){
        return Db::table($this->table)->where(['id'=>$id])->update($data) ;
    }


    //根据订单ID查找详情
    public  function  getOrderInfoById($id){
        return Db::table($this->table)->where(['id'=>(int)$id])->find();
    }

     //查找下单超时未付款的订单
    public function getOverTimeOrder($serch_time){
        return Db::table($this->table)
            ->field('id,open_id')
            ->where(['status'=>0,'create_time'=>['<=',$serch_time]])
            ->select();
    }

    //查找下单付款成功，未推送至oms系统
    public function  getPushOmsInfo(){
        return Db::table($this->table)
            ->field('*')
            ->where(['status'=>1,'is_push_oms'=>0])
            ->select();
    }

    //关闭超时未付款的订单
    public function closeOverTimeOrder($id){
        return Db::table($this->table)->where(['status'=>0,'id'=>$id])->update(['status' => 3,'timeout_time'=>time()]) ;
    }

    //更改推送oms订单状态
    public function updateOmsStatus($id,$status,$msg){
        return Db::table($this->table)->where(['id'=>$id])->update(['is_push_oms' =>$status,'oms_msg'=>$msg,'modify_time'=>time()]) ;
    }

    //查看用户是否下过单
    public  function  getOrderBeforeByUid($open_id){
        return Db::table($this->order_before_table)->where(['open_id'=>$open_id])->find();
    }

    //删除并发表记录
    public  function  delOrderBeforeByUid($open_id){
        return Db::table($this->order_before_table)->where(['open_id'=>$open_id])->delete();
    }

    //防并发表添加
    public function addOrderBeforeByUid($open_id){
        return Db::table($this->order_before_table)->insert(['open_id'=>$open_id]);
    }

    //查看库存表
    public  function  viewInventory(){
        return Db::table($this->inventory_table)->find();
    }

    //库存表增加
    public function  incrInventory(){
        return Db::table($this->inventory_table)->where('residue_inventory<inventory')->setInc('residue_inventory',1);
     }

    //库存表递减
    public function  decrInventory(){
        return Db::table($this->inventory_table)->where('residue_inventory>0')->setDec('residue_inventory',1);
    }

    //剩余库存修改
    public function  updateInventory($residue_inventory){
        return Db::table($this->inventory_table)->where('residue_inventory<=inventory')->update(['residue_inventory' =>$residue_inventory]) ;
    }

}

?>
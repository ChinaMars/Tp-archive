<?php
namespace app\index\Model;
use think;
use Think\Db;
use think\Model;


class Region extends Model
{
    protected $table =   'givenchy_region';//区域表

    //获取所有省份
    public  function  getAllProvice(){
        return  Db::table($this->table)
            ->field('local_name,region_id')
            ->where('region_grade','=',1)
            ->select();
    }

    //获取父级下的子集
    public function getChildArea($pid){
        return Db::table($this->table)
            ->field('local_name,region_id,p_region_id')
            ->where('p_region_id','=',$pid)
            ->select();
    }


}

?>
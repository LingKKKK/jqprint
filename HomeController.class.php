<?php
namespace Admin\Controller;
use Think\Controller;
class HomeController extends MainController {
	private $side;
	
public function order_print(){
		$db_order = M('user_order');
		$db_order_goods = M('user_order_goods');
		$db_worker = M('worker');
		
		$db_sys=M('system');
		
		$TID = intval($_GET['TID']);
		if($TID>0){
			$re_order = $db_order->alias('a')->field('a.TID,a.AddTime,a.Actually_pay,a.contact_name,a.freightage_pay,a.contact_phone,a.address,a.worker_id,a.area_id,a.status,a.coupon_pay,a.score_used,a.worker_percentage,a.PayTime,a.show_id,b.user_name,c.area_name')->join('LEFT JOIN zp_user b on a.user_id=b.TID')->join('LEFT JOIN zp_area c on a.area_id=c.TID')->where('a.TID='.$TID)->find();
//			echo $db_order->getLastSql();die;
			$re_order['PayTime'] = empty($re_order['PayTime'])?'暂未付款':date('Y-m-d G:i',$re_order['PayTime']);
			$re_order['goods'] = $db_order_goods->field('goods_name,number,price')->where('order_id='.$TID)->select();
			
			foreach($re_order['goods'] as $key=>$goods){
				$re_order['goods'][$key]['goods_small'] = '<img src="'.$goods['goods_small'].'" width="60px"/>';
			}
//			$re_order['status'] = $status[$re_order['status']];
			$worker_list = $db_worker->field('TID,NickName workername')->where('AreaID='.$re_order['area_id'].' and status<>2')->select();
			
			$phone=$db_sys->where('TID=1')->getField('phone');
//			echo $db_order_goods->getLastSql();die;
//			dump($re_order);die;
//			echo $db_admin->getLastSql();die;
			$this->assign('status',C('ORDER_STATUS'));
//			$this->assign('worker_list',$worker_list);
			$this->assign('order',$re_order);
			$this->assign('phone',$phone);
		}else{
			$this->error('请选择订单');
		}

		$this->display('Order/print');
	}
//eof 数据库操作

}
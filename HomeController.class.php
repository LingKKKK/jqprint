<?php
namespace Admin\Controller;
use Think\Controller;
class HomeController extends MainController {
	private $side;
	
	public function  _initialize(){
		$db_admin = M('admin');
		$db_roll = M('admin_role');
		$db_resource = M('admin_resource');
	//	dump(C('EXCEPTION'));die;
	//	echo ACTION_NAME;die;
//		echo CONTROLLER_NAME;die;
		$exception = C('EXCEPTION');
		$action = ACTION_NAME;
		//检查session和role
		if(!in_array($action,$exception)){
			$aid = $_SESSION['TID'];
//			echo $aid;die;
			if(!$aid){
				$this->signin();
			}

			$resource_list = $db_admin->alias('a')->field('b.Indexes')->join('LEFT JOIN zp_admin_role b on a.RoleID=b.TID')->where('a.TID='.$aid)->find();
			
			$side_list = $db_resource->field('TID,Title,Url,Icon')->where('PID=0 and TID in ('.$resource_list['Indexes'].')')->order('OrderID asc , TID asc')->select();			
			$Footer = $db_resource->where('Url="'.ACTION_NAME.'"')->getField('Footer');
//			echo $db_resource->getLastSql();die;
			foreach($side_list as $key=>$side){
				$children = $db_resource->field('Title,Url')->where('PID='.$side['TID'].' and TID in ('.$resource_list['Indexes'].')')->order('OrderID asc , TID asc')->select();

				if($children){
					foreach($children as $k=>$child){
						if($Footer == $child['Url']){
							$children[$k]['footer'] = 'class="active"';
							$side_list[$key]['footer'] = 'class ="active open"';
							break;
						}
					}
					$side_list[$key]['children'] = $children;
					
				}else{
					$side_list[$key]['children'] = array();
				}
			}
			

//			dump($side_list);die;
//			$this->side = $side_list;
			
			$this->assign('side',$side_list);
			$action = C(ACTION_NAME);
			if($action){
				$this->$action['action']($action);die;
			}
		}
	}
	//bof app版本
	public function app_version(){
	   	$db=M('app_version');
		$list=$db->field('*')->find(1);
		$this->assign('info',$list);
		
	    $this->display('System/app_version');
	}
	public function app_version_save(){
//		dump($_POST);die;
		$AndroidVersion=trim(strval($_POST['AndroidVersion']));
		$AndroidUrl=trim(strval($_POST['AndroidUrl']));
		
		$IOSVersion=trim(strval($_POST['IOSVersion']));
		$IOSUrl=trim(strval($_POST['IOSUrl']));
		
	
	    $info=array(
	        'AndroidVersion'=>$AndroidVersion,
	        'AndroidContent'=>trim($_POST['AndroidContent']),
	        'AndroidUrl'=>$AndroidUrl,
	        'IOSVersion'=>$IOSVersion,
	        'IOSContent'=>trim($_POST['IOSContent']),
	        'IOSUrl'=>$IOSUrl,
	        'EditTime'=>$_SERVER['REQUEST_TIME']
	    );
		
	    $db=M('app_version');
		$ID=1;
	    if($ID>0){
	        $info['TID']=$ID;
	        $res=$db->save($info);
	        if($res!==false){
	            $this->success('修改成功');
	        }else{
	            $this->error('修改失败');
	        }
	    }else{
	        $info['AddTime']=$_SERVER['REQUEST_TIME'];
	        $res=$db->add($info);
	        if($res>0){
	            $this->success('添加成功',cookie('back_list'));
	        }else{
	            $this->error('添加失败');
	        }
	    }
	
	}
	//eof app版本
		
	//bof 平台权限管理
	
	//管理节点列表
	public function manager_list(){
		$this->display('Resource/list');
	}
	
	public function manamger_edit(){
		$db_resource = M('admin_resource');
		$TID = intval($_GET['TID']);
		if($TID>0){
		}
		$this->display();
	}
	
	public function manager_save(){
		$db_resource = M('admin_resource');
		$info = array();
		
		$TID = intval($_POST['TID']);
		if($TID>0){
		}else{
		}
	}
	
	//删除管理员
	public function manager_del(){
		$TID = intval($_GET['TID']);
		$db_resource = M('admin_resource');
		if($TID>0){
			$count = $db_resource->where('PID='.$TID)->count(0);
			if($count>0){
				$this->error('请先移除子模块，才能删除该功能模块');die;
			}
			if($db_resource->delete($TID)!==false){
				$this->success('模块删除成功');die;
			}else{
				$this->error('模块删除失败');die;
			}
		}else{
			$this->error('权限不存在');
		}
	}
	////bof 管理员个人信息管理
	public function profile(){
//		dump($_SESSION);die;
		$db=M('admin');
		$info=$db->alias('a')->field('b.Title as RoleName,a.*')->join('zp_admin_role as b on b.TID=a.RoleID')->where('a.TID='.$_SESSION['TID'])->find();
//		echo $db->getLastSql();die;
		$this->assign('info',$info);
		$this->display('Manager/profile');
	}
	public function profile_save(){
		$ID=intval($_POST['ID']);
		$NikeName=strval($_POST['NikeName']);
		$Password1=strval($_POST['Password1']);
		$Password2=strval($_POST['Password2']);
		
		$data=array();
		if($ID>0){
			$data['TID']=$ID;	
		}
		$data['NikeName']=$NikeName;
		
		if(empty($Password1)&&$ID==0){
			$this->error('密码不能成功！');die;
		}
		
		if(!empty($Password1)){
			if($Password1!=$Password2){
				$this->error('两次密码不一致！');die;
			}else{
				$Password=$this->get_pwd($Password1);
				$data['Password']=$Password;
			}
			
		}
		
		$db=M('admin');
		if($ID>0){
//			$data['ModifyTime']=$_SERVER['REQUEST_TIME'];
			$res=$db->save($data);
			if($res!==false){
				$this->success('修改成功',U('profile',array('ID'=>$ID)));
			}else{
				$this->error('修改失败');
			}
		}else{
			//忽略添加
			$this->error('非法账号,忽略操作');die;
		}
	}	
	//bof 资源管理
	
	//管理节点列表
	public function resource_list(){
		cookie('back_list',__SELF__.'#resource_list');
		$this->display('Resource/list');	
	}
	
	public function resource_tree($PID=0){
		$Indexed=trim(strval($_GET['Indexes']));
		//echo $Indexed;
		$Indexed_arr=array();
		if(!empty($Indexed)){
		$Indexed_arr=explode(',',$Indexed);
		}
				
		$db=M('admin_resource');	
		$list=$db->where('PID='.$PID)->order('OrderID asc ,TID asc')->select();
		$data_arr=array();
		foreach($list as $item){
			$data_arr[$item['Title']]=array(
				'text'=>$item['Title'].'-'.$item['OrderID'],
				'ID'=>$item['TID']
			);
			if(in_array($item['TID'],$Indexed_arr)){
				$data_arr[$item['Title']]['additionalParameters']['item-selected']=true;
			}
			if($db->where('PID='.$item['TID'])->count(0)<=0){
				$data_arr[$item['Title']]['type']='item';
			}else{
				$data_arr[$item['Title']]['type']='folder';		
				$data_arr[$item['Title']]['additionalParameters']['children']= $this->resource_tree($item['TID']);
			}
		}
		if($PID==0){
			print_r(json_encode($data_arr));
		}else{
			return $data_arr;
		}
	}
	
	public function resource_edit(){
		$ID=intval($_GET['ID']);
		$PID=intval($_GET['PID']);
		$actionName='';
		$info=array();
		$db=M('admin_resource');
		if($ID>0){
			$info=$db->find($ID);
			$actionName='编辑';	
		}else{
			$info['PID']=$PID;
			$actionName='添加';
		}
		$this->assign('info',$info);
		
		$list=$db->where('PID=0')->order('OrderID asc,TID asc')->select();
//		echo $db->getLastSql();die;
		$this->assign('resource_list',$list);
		
		$this->assign('actionName',$actionName);
		$this->assign('back_list',cookie('back_list'));
		$this->display('Resource/edit');die;
	}
	
	public function resource_save(){
		$ID=intval($_POST['ID']);
		$PID=intval($_POST['PID']);
		$Title=trim(strval($_POST['Title']));
		$Url=trim(strval($_POST['Url']));
		$Icon=trim(strval($_POST['Icon']));
		$OrderID=intval($_POST['OrderID']);
		
		$data=array(
			'PID'=>$PID,
			'Title'=>$Title,
			'Url'=>$Url,
			'Icon'=>$Icon,
			'OrderID'=>$OrderID
		);
		if(empty($_POST['Footer'])){
			$data['Footer']=$data['Url'];
		}else{
			$data['Footer']=trim($_POST['Footer']);
		}
		$db=M('admin_resource');
		
		if($ID>0){
			$data['TID']=$ID;
			$PID_old=$db->where('TID='.$ID)->getField('PID');
			$res=$db->save($data);
			
//			echo $db->getLastSql();die;
			
			if($res!==false){
				$this->resource_updata($PID,$PID_old);
				$this->success('修改成功',$_SERVER['HTTP_REFERER'].'#resource_list');
			}else{
				$this->error('修改失败');
			}
		}else{
			$res=$db->add($data);
			if($res>0){
				$this->resource_updata($PID);
				$this->success('添加成功',cookie('back_list'));
			}else{
				$this->error('添加失败');
			}
		}
		
	}
	private function resource_updata($PID=0,$PID_old=0){
		$db=M('resource');
		if($PID>0){
			$count=$db->where('PID='.$PID)->count(0);
			$data=array('TID'=>$PID,'ChildNum'=>$count);
			$db->save($data);
		}
		if($PID_old>0){
			$count=$db->where('PID='.$PID_old)->count(0);
			$data=array('TID'=>$PID_old,'ChildNum'=>$count);
			$db->save($data);
		}		
	}
	
	public function resource_del(){
//		print_r($_GET);die;
		$ID=intval(trim($_GET['ID']));
		$db=M('admin_resource');
		//ChildNum 维护
		$PID=$db->where('TID='.$ID)->getField('PID');
		if($PID>0){
			$count=$db->where('PID='.$PID)->count(0);
			$data=array('TID'=>$PID,'ChildNum'=>$count);
			$db->save($data);
		}
		
		$result=$db->delete($ID);
		if($result){
			$this->success('操作成功！',$_SERVER['HTTP_REFERER'].'#resource_list');
		}else{
			$this->error('删除错误！');
		}
	}
	
	//eof 资源管理
	
	//bof 权限管理
	public function manager_role_list(){
		import('ORG.Util.Page');
		cookie('back_list',__SELF__.'#manager_role_list');
		$pageSize=10;
		$db=M('admin_role');
		$db_admin=M('admin');
		$db_role=M('site_role');
		$where='';
		$count= $db->where($where)->count();
		
		$Page		= new \Think\Page($count,$pageSize);
		$nowPage	= isset($_GET['p'])?$_GET['p']:1;
		$data		= $db->where($where)->order('TID desc')->page($nowPage.','.$Page->listRows)->select();
		$show		= $Page->show();
		
		$list_data=array();
		foreach($data as $item){
			$role_name_array=array();
			$role_name_array=$db_role->where("TID in (".$item['Indexes'].")")->getField('Title',true);
			$item['RoleName']=implode('，',$role_name_array);
			$item['AdminCount']=$db_admin->where('RoleID='.$item['TID'])->count(0);
			$list_data[]=$item;
		}
		
		$this->assign('page',$show);
		$this->assign('list_data',$list_data);
		$this->display('Manager/manager_role_list');die;
	}
	
	
	public function manager_role_edit(){
		$ID=intval($_GET['ID']);
		$actionName='';
		$info=array();
		if($ID>0){
			$db=M('admin_role');
			$info=$db->find($ID);
			$actionName='编辑';	
		}else{
			$actionName='添加';
		}
		
		$this->assign('info',$info);
		
		$db_role=M('admin_role');
		$roles=$db_role->field('TID,Title')->select();
		$this->assign('roles',$roles);
		
//		$db_indexes=M('admin_role_indexes');
//		$indexes=$db_role->where('TID='.$ID)->getField('SiteRoleID',true);
		$this->assign('indexes',$info['indexes']);
		
		$this->assign('actionName',$actionName);
		$this->assign('back_list',cookie('back_list'));
		$this->display('Manager/manager_role_edit');die;
	}
	
	public function manager_role_save(){
		$ID=intval($_POST['ID']);
		$Title=strval($_POST['Title']);
		$Indexes=$_POST['Indexes'];
		$indexes_arr='';
		if(!empty($Indexes)){
			$indexes_arr=explode(',',$Indexes);
		}
		//dump($indexes_arr);
		//检测父级
		$db_role=M('admin_resource');
		$pid_arr=$db_role->where('TID in ('.$Indexes.')')->group('PID')->getField('PID',true);
		$indexes_arr=array_merge($indexes_arr,$pid_arr);
		
		$indexes_arr=array_unique($indexes_arr);
		//dump($indexes_arr);die;
		
		$Indexes=implode(',',$indexes_arr);
		
		$info=array();
		$info['EditTime']=$_SERVER['REQUEST_TIME'];
		$info['Title']=$Title;
		$info['Indexes']=$Indexes;
		$db=M('admin_role');
		if($ID>0){
			$info['TID']=$ID;
			
			$res=$db->save($info);
			if($res!==false){
				$this->success('修改成功',U('manager_role_edit',array('ID'=>$ID)).'#manager_role_list');
			}else{
				$this->error('修改失败');
			}
				
		}else{
			$info['AddTime']=$info['EditTime'];
			$res=$db->add($info);
			if($res>0){
				$this->success('添加成功',cookie('back_list'));
			}else{
				//echo $db->getLastSql();die;
				$this->error('添加失败');
			}
		}	
	}
	
	public function manager_role_del(){
		
		
		$ids="";
		if($_POST){
			//echo $_POST['ID'];
			$ids=implode(',',$_POST['ID']);
		}else{
			$ids=strval(trim($_GET['ID']));
		}
		if(empty($ids)){
			$this->success('操作失败！',$_SERVER['HTTP_REFERER'].'#manager_role_list');
		}
		$db=M('admin_role');
		$db_admin=M('admin');
		//检测是否有管理员正在使用改角色
		$count=$db_admin->where('RoleID in ('.$ids.')')->count();
		if($count>0){
			$this->success('管理员正在使用该权限，不可删除！',$_SERVER['HTTP_REFERER'].'#manager_role_list');
		}
		$result=$db->delete($ids);
		if($result){
			$this->success('操作成功！',$_SERVER['HTTP_REFERER'].'#manager_role_list');
		}else{
			$this->error('删除错误！');
		}
	}
	//eof 权限管理

	//登录页面
    public function signin(){
		$this->display('Admin/signin');die;
    }
	

	
	//检查登录(分为不同的角色登录 1-后台管理员 2-玩商)
	public function signin_check(){	

		//验证成功之后会释放掉当前验证码
		if($this->verify_check($_POST['verify'])){
			$db_admin = M('admin');

			$pwd = md5($_POST['password']);
			$info = array('UserName'  => $_POST['admin'],
						  'Password'  => $pwd);

			$re_admin = $db_admin->field('TID,RoleID,AID,IsBlack')->where($info)->find();

			if($re_admin){
				if($re_admin['IsBlack']=='1'){
					$this->error('你已经被禁用,无法访问后台管理系统',U('Admin/signin'));die;
				}

				session('TID',$re_admin['TID']);
				session('RoleID',$re_admin['RoleID']);


				$this->success('登录成功',U('Home/home_page'));
			
			}else{
				$this->error('账号密码错误',U('Home/signin'));
			}
		}else{
			$this->error('验证码错误',U('Home/signin'));
		}
	}
	
	//退出
	public function signout(){
		$UID = strval($_COOKIE['admin']);
		if(isset($_SESSION[$UID])){
			unset($_SESSION[$UID]);
		}
		$this->success('退出成功',U('Home/signin'));
	}

	//首页
	public function home_page(){
		
		//统计数量
		$db_user=M('user');
		$db_order=M('user_order');
		$db_com = M('user_complain');
		$db_hot = M('hot_search');
		$db_store=M('area_goods');
		//用户数
		$count['user']=$db_user->count(0);
		//男女比例
		$man=$db_user->where('gender=1')->count(0);
		$woman=$db_user->where('gender=2')->count(0);
		$count['ratio']=$man.':'.$woman;
		//待处理订单
		$count['order']=$db_order->where('status=2')->count(0);
		//当天交易额
		$today=date('Y-m-d');
		$today=strtotime($today);
		$count['sum']=floatval($db_order->where('Status>3 and AddTime>='.$today)->sum('Actually_pay'));
		
		//用户投诉
		$count['com']=$db_com->count(0);
		
		//热门搜索词
		$count['hot']=$db_hot->order('times desc, OrderID')->getField('keyword');
		
		//库存报警
		$count['store']=$db_store->where('store_count<5')->count(0);
//		echo $db_hot->getLastSql();
//		echo $today;die;
//		echo $db_order->getLastSql();die;
//		dump($count);die;
		$this->assign('count',$count);
		
		$this->display('Home/home_page');
	}
	
	
	
	//bof 喝点相关管理方法
//首页信息管理
	public function index_list(){
		$this->track['index'][0] = 'class= "active open"';
		$this->track['index']['list']='class = "active"';
		$this->assign('track',$this->track);
		$cate = array(
			'0' => '无分类',
			'1' => '同城推荐(唯一)',
			'2' => '酒馆(多个)');
		$db_index = M('index_data');
		//分页
		$count = $db_index->alias('a')->where($info)->count(0);
		
		$this->page_set($count,15,'个板块',array());
		$index_list = $db_index->alias('a')->field('a.TID,a.title,a.OrderID,a.AddTime,a.EditTime,a.area_id,b.area_name,a.cate')->join('LEFT JOIN zp_area b on a.area_id = b.TID')
							->order('a.area_id,a.cate,a.OrderID')
							->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_index->getLastSql();die;
		foreach($index_list as $k=>$v){
			$index_list[$k]['cate'] = $cate[$v['cate']];			
		}
		//站点列表
		$area_list = M('area')->field('TID,area_name')->select();
		$this->assign('page',$this->page->show());		
		$this->assign('area_list',$area_list);
		$this->assign('index',$index_list);
		$this->display('Index/list');
	}
	
	public function index_edit(){
		$this->track['index'][0] = 'class= "active open"';
		$this->track['index']['edit']='class = "active"';
		$this->assign('track',$this->track);		
		$cate = array(
			'0' => '无分类',
			'1' => '同城推荐(唯一)',
			'2' => '酒馆(多个)');
		if(isset($_GET['TID'])){
			$TID = intval($_GET['TID']);
			$db_index = M('index_data');
			$re_index = $db_index->field('TID,title,cate,area_id,OrderID,GoodsID,SexID,PettyID')->find($TID);
			$this->assign('index_data',$re_index);
		}
		$db_area = M('area');
		$db_goods = M('area_goods');
		$area_list = $db_area->field('TID,area_name')->select();
		
		$goods_list = $db_goods->alias('a')
							->field('a.TID,b.title,c.type')
							->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
							->where('a.is_onsale=1 and b.type<>4 and a.area_id='.$re_index['area_id'])
							->select();
							
//		echo $db_area->getLastSql();die;
//		echo $db_goods->getLastSql();die;
		$this->assign('goods_list',$goods_list);
		$this->assign('area',$area_list);
		$this->assign('cate',$cate);		
		$this->display('Index/edit');
	}
	
	public function index_save(){
//		dump($_POST);die;	
		$db_index = M('index_data');
		$info = array(
			'title'    => strval($_POST['title']),
			'area_id'  => intval($_POST['area_id']),
			'cate'     => intval($_POST['cate']),
			'OrderID'  => intval($_POST['OrderID']),
			'EditTime' => $_SERVER['REQUEST_TIME']);
		
		if($info['cate']==0){
			$this->error('请选择所在分类');die;
		}else if($info['cate']==1){
			$info['SexID']='0,'.implode(',',$_POST['SexID']).',0';
			$info['PettyID']='0,'.implode(',',$_POST['PettyID']).',0';
		}else{
			$info['GoodsID']='0,'.implode(',',$_POST['GoodsID']).',0';
		}
		
		if($info['area_id']==0){
			$this->error('请选择站点');die;
		}
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			if($info['cate']==1){
				$check = $db_index->field('TID')->where('area_id='.$info['area_id'].' and cate='.$info['cate'])->select();
				//echo $db_index->getLastSql();die;
				if($check[0]['TID']!=$TID && !empty($check)){
					$this->error('每个站点只有一个同城推荐板块');die;
				}
			}
			//删除原来图片
			if(isset($info['image'])){
				$del_img = $db_index->field('image')->find($TID);
				$this->file_del($del_img['image']);
			}
			$re_index = $db_index->save($info);
		}else{
			if($info['cate']==1){
				$count = $db_index->where('area_id='.$info['area_id'].' and cate='.$info['cate'])->count(0);
				if($count>0){
					$this->error('每个站点只有一个同城推荐板块');die;
				}
			}
			$info['AddTime'] = $info['EditTime'];
			$re_index = $db_index->add($info);
		}
		if($re_index!== false){
			$this->success('保存板块成功',U('Home/index_list'));
		}else{
			$this->error('保存板块失败');
		}
	}
	
	public function index_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_index = M('index_data');
			if($db_index->delete($TID)!=false){
				$this->success('删除板块成功');die;
			}else{
				$this->error('删除板块失败失败');die;
			}
		}else{
			$this->success('删除成功',U('Home/index_list'));
		}
	}
	
//首页元素管理
	public function index_element_list(){
		$this->track['index'][0] = 'class= "active open"';
		$this->track['index']['element_list']='class = "active"';
		$this->assign('track',$this->track);
		
		$link = array(
			'0' => '无链接',
			'1' => '商品详情',
			'2' => '活动详情',
			'3' => '套餐列表',
			'4' => '自由单点列表');
		
		$position = array(
					'1' => '首部轮播(图片)',
//					'2' => '酒馆板块',
					'3' => '套餐板块(图片)',
					'4' => '自由单点(图片)',
					'5' => '中部广告(图片)',
					'6' => '小资看点(标题)',
					'7' => '底部轮播(图片)',
					'8' => '性感的酒(标题)');	
			
		$TID = intval($_GET['TID']);
		$db_index_element = M('index_element');		
		if($TID>0){
			$info['a.index_id'] = $TID;
		}

		//分页
		$count = $db_index_element->alias('a')->where($info)->count(0);				

		$this->page_set($count,15,'元素信息',array());
		
		$element_list = $db_index_element->alias('a')->field('a.TID,a.title,a.position,a.image,a.AddTime,a.EditTime,a.OrderID,a.index_id,a.link,b.title index_name,c.area_name')->join('LEFT JOIN  zp_index_data b on a.index_id=b.TID')->join('LEFT JOIN zp_area c on b.area_id=c.TID')->where($info)->order('a.index_id,a.OrderID')->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_index_element->getLastSql();die;
		foreach($element_list as $k=>$t){
			$element_list[$k]['position'] = $position[$t['position']];
			$element_list[$k]['link'] = $link[$t['link']];
			if(!empty($t['image'])){
				$element_list[$k]['image'] = '<img width="50px" src="'.$t['image'].'"/>';
			}else{
				$element_list[$k]['image'] = '无图片';
			}
		}
		$this->assign('element_list',$element_list);
		$this->assign('page',$this->page->show());
		$this->display('Index/element_list');		
	}
	
	public function index_element_edit(){
		$this->track['index'][0] = 'class= "active open"';
		$this->track['index']['element_save']='class = "active"';
		$this->assign('track',$this->track);
		
		$link = array(
			'0' => '无链接',
			'1' => '商品详情',
			'2' => '活动详情',
			'3' => '套餐列表',
			'4' => '自由单点列表',
//			'5' => '套餐详情'
			);
		
		$gender = array(
			'0' => '所有人',
			'1' => '男客户',
			'2' => '女客户');	
			
		$coupon =array(
			'0' => '否',
			'1' => '是');
		
			
		$position = array(
					'1' => '首部轮播(图片)',
//					'2' => '酒馆板块',
					'3' => '套餐板块(图片)',
					'4' => '自由单点(图片)',
					'5' => '中部广告(图片)',
					'6' => '小资看点(标题)',
					'7' => '底部轮播(图片)',
					'8' => '性感的酒(标题)');
				
		$db_index_element = M('index_element');
		$db_index = M('index_data');
		$TID = 	intval($_GET['TID']);
		if($TID>0){
			$element = $db_index_element->alias('a')
							->field('a.TID,a.index_id,a.title,a.image,a.position,a.OrderID,a.link,a.content,a.send_coupon,a.coupon_id CID,b.TimeLimit,b.pay')
							->join('LEFT JOIN zp_coupon b on a.coupon_id=b.TID')
							->where('a.TID='.$TID)->find();

			$this->assign('element',$element);
		}
		//站点板块列表
		$index_list = $db_index->field('a.TID,a.title,b.area_name')->alias('a')->join('LEFT JOIN zp_area b on a.area_id=b.TID')->select();		
		$this->assign('gender',$gender);
		$this->assign('index_list',$index_list);
		$this->assign('link_list',$link);
		$this->assign('position',$position);
		$this->assign('send_coupon',$coupon);

		//属性获取
		$cate = $this->cate_ergodic();		
		$this->assign('cate',$cate);	
		
		$this->display('Index/element_edit');
	}
	
	public function index_element_save(){
		$position = array(
					'1' => '首部轮播(图片)',
//					'2' => '酒馆板块',
					'3' => '套餐板块(图片)',
					'4' => '自由单点(图片)',
					'5' => '中部广告(图片)',
					'6' => '小资看点(标题)',
					'7' => '底部轮播(图片)',
					'8' => '性感的酒(标题)');
		
		$alone = array(4,5,6,8);  //该部分只需要一张图片或标题
//		print_r($_POST);die;
		$db_index_ele = M('index_element');
		$db_index = M('index_data');
		$db_coupon= M('coupon');
		$info = array(
			'index_id' => intval($_POST['index_id']), //主题所在部分
			'title'    => trim(strval($_POST['title'])),   //标题
			'position' => intval($_POST['position']),      //所在位置
			'link'     => intval($_POST['link']),          //连接位置
			'send_coupon'=> intval($_POST['send_coupon']), //是否发放优惠券
			'EditTime' => $_SERVER['REQUEST_TIME']);
		if($info['link'] == 2){
			$info['content'] = trim(strval($_POST['content']));
		}
		
		if($info['index_id']==0){
			$this->error('请选择所在主题');die;
		}
		if($info['position']==0){
			$this->error('请选择所在位置');die;
		}		
	
		$cate =$db_index->field('cate')->find($info['index_id']);
//		echo $db_index->getLastSql();die;
		if($cate['cate']==2 && $info['position']!=1){
			$this->error('酒馆板块只需设置首部轮播图片');die;
		}		
		//保存图片
		$upload = $this->upload('Index/');
		if(!empty($upload)){
			$info['image'] = $this->rootpath.$upload['0']['savepath'].$upload['0']['savename'];
		}
		
		//添加优惠券信息
		if($info['send_coupon'] == 1){
			$coupon = array(
				'pay'          => floatval($_POST['pay']),
				'pay_limit'    => floatval($_POST['pay_limit']),
				'TimeLimit'    => intval($_POST['limit']),
				'coupon_gender'=> intval($_POST['gender']),
				'SendTime'     => 4,
				'EditTime'     => $info['EditTime']);
				
			if(empty($coupon['pay'])){
				$this->error('请输入正确金额');die;
			}
			if($coupon['TimeLimit']<=0){
				$this->error('期限时间必须大于0');die;
			}
			$area = $db_index->field('area_id')->find($info['index_id']);
	//		echo $db_index->getLastSql();die;
			//获取站点信息
			if($area==false){
				$this->error('无法获取所在站点');die;
			}
			$coupon['coupon_area'] = $area['area_id'];
			
			if(in_array(0,$_POST['cate_limit']) || empty($_POST['cate_limit'])){
				$coupon['cate_limit'] = 0;
			}else{
				$coupon['cate_limit'] = implode(',',$_POST['cate_limit']);
			}			
			
			$CID = intval($_POST['CID']);
			if($CID>0){
				$coupon['TID'] = $CID;
				$info['coupon_id'] = $CID;
				$re_coupon = $db_coupon->save($coupon);	
				if($re_coupon!==false){
					$this->success('保存消息成功',U('Home/Coupon_list'));die;
				}else{
					$this->error('保存消息失败');die;
				}			
			}else{
				$coupon['AddTime'] = $coupon['EditTime'];
				$re_coupon = $db_coupon->add($coupon);
	//			echo $db_mes->getLastSql();die;
				//根据条件判断是否发送消息
				if($re_coupon>0){
					$info['coupon_id'] = $re_coupon;
				}else{
					$this->error('保存相关优惠券失败');die;
				}			
			}
		}else{
			$db_coupon->delete(intval($_POST['CID']));
			$info['coupon_id'] = 0;
		}		
		
		$TID = intval($_POST['TID']);
		if(in_array($info['position'],$alone)){
			$old = $db_index_ele->field('TID')->where('index_id='.$info['index_id'].' and position ='.$info['position'])->find();
			if($old!==false){
				$TID = $old['TID'];
			}
		}
		
		
		if($TID>0){
			//删除原来图片
			if(isset($info['image'])){
				$del_img = $db_index_ele->field('image')->find($TID);
				$this->file_del($del_img['image']);
			}
			$info['TID'] = $TID;
			$re_ele = $db_index_ele->save($info);
		}else{
			$info['AddTime'] = $info['EditTime'];
			$re_ele = $db_index_ele->add($info);
		}
//		echo $db_index_ele->getLastSql();die;
		if($re_ele!== false){
			$this->success('保存板块元素成功',U('Home/index_element_list',array('TID'=>$info['index_id'])));
		}else{
			$this->error('保存板块元素失败');
		}		
	}

//首页元素删除
	public function index_element_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_index_element = M('index_element');			
			$del = $db_index_element->field('image')->find($TID);
			$this->file_del($del);			
			if($db_index_element->delete($TID)!==false){
				$this->success('删除站点元素成功',U('Home/index_element_list'));
			}else{
				$this->error('删除站点元素失败',U('Home/index_element_list'));
			}
		}else{
			$this->error('请选择有效的站点');
		}
	}
	
//自由单点设置
	public function free_list(){
		$this->track['free'][0] = 'class= "active open"';
		$this->track['free']['list']='class = "active"';
		$this->assign('track',$this->track);
				
		$db_nav = M('free_nav');
		
		//分页
		$count = $db_nav->alias('a')->where($info)->count(0);
		
		$this->page_set($count,15,'自由单点属性',array());		
		
		$free_list = $db_nav->alias('a')->field('a.TID,a.nav_name,b.area_name,a.AddTime,a.EditTime,a.OrderID')->join('LEFT JOIN zp_area b on a.area_id = b.TID')->limit($this->page->firstRow,$this->page->listRows)->select();
		$this->assign('page',$this->page->show());
		$this->assign('free_list',$free_list);
		$this->display('Free/list');
	}
	
	public function free_edit(){
		$this->track['free'][0] = 'class= "active open"';
		$this->track['free']['edit']='class = "active"';
		$this->assign('track',$this->track);	
		$db_area = M('area');
		$db_nav = M('free_nav');
		
		$db_goods = M('area_goods');
		
		$TID = intval($_GET['TID']);		
		
		$area_list = $db_area->field('TID,area_name')->select();
//		echo $db_area->getLastSql();die;
		if($TID>0){
			$info = $db_nav->field('TID,area_id,nav_name,OrderID,AboutID')->find($TID);
//			$goods_list = $db_goods->field('TID,goods_name,unit,sold_amount')->where('is_onsale=1 and area_id='.$info['area_id'])->select();
			$goods_list = $db_goods->alias('a')
					->field('a.TID,b.title,c.type')
					->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
					->where('a.is_onsale=1 and b.type<>4 and a.area_id='.$info['area_id'])
					->select();
		}
//		echo $db_goods->getLastSql();die;
//		dump($goods_list);die;
		$this->assign('info',$info);
		$this->assign('goods_list',$goods_list);
		$this->assign('area',$area_list);
				
		$this->display('Free/edit');
		
	}
	
	public function free_save(){
//		dump($_POST);die;
		$db_nav = M('free_nav');
		$info = array(
			'nav_name' => trim(strval($_POST['nav_name'])),
			'OrderID'  => intval($_POST['OrderID']),
			'area_id' => intval($_POST['area_id']),
			'EditTime' => $_SERVER['REQUEST_TIME']);
		if($info['area_id']<=0){
			$this->error('请选择正确的站点');die;
		}
		if(!empty($_POST['AboutID'])){
			$info['AboutID']='0,'.implode(',',$_POST['AboutID']).',0';
		}else{
			$info['AboutID']='0';
		}
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			if($db_nav->save($info)!==false){
				$this->success('修改导航元素成功',U('Home/free_list'));
			}else{
				$this->error('修改自由单点导航元素失败');
			}
		}else{
			$info['AddTime'] = $info['EditTime'];
			if($db_nav->add($info)>0){
				$this->success('添加自由单点元素成功',U('Home/free_list'));
			}else{
				$this->error('添加自由单点导航元素失败');
			}
		}
	}
	
	public function free_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_free = M('free_nav');			
			if($db_free->delete($TID)!==false){
				$this->success('删除自由单点元素成功');
			}else{
				$this->error('删除自由单点元素失败');
			}
		}else{
			$this->error('请选择有效的自由单点元素');
		}
		
	}
	
	public function ajax_goods_list(){
		$area_id = I('area_id');
		$db_goods = M('area_goods');
		$goods_list = $db_goods->alias('a')
						->field('a.TID,b.title,c.type')
						->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
						->where('a.is_onsale=1 and b.type<>4 and a.area_id='.$area_id)
						->select();
		if(empty($goods_list)){
			$goods_list=array();
		}
		echo json_encode($goods_list);
	}
	
//用户查看
	public function user_list(){
//		dump($_GET);die;
		if(!empty($_GET['creat_table'])){
			$this->user_excel();
		}
		if(!empty($_GET['search'])){
			$ser['search']=1;
			if(!empty($_GET['phone'])){
				$where['a.user_phone']=array('like','%'.trim($_GET['phone']).'%');
				$ser['phone'] = trim($_GET['phone']);
			}
			$gender=intval($_GET['gender']);
			if($gender!=0){
				$where['gender']=$gender;
				$ser['gender']=$gender;
			}
			list($start,$end)=explode('到',$_GET['time-range']);
			$ser['time-range']=trim($_GET['time-range']);
			if(!empty($start) && !empty($end)){
				$start=strtotime($start);
				$end=strtotime($end);
				$paytime = 'and b.PayTime BETWEEN '.$start.' AND '.$end;
//				$where['b.PayTime']=array('between',array($start,$end));
			}
			if(!empty($_GET['rate'])){
				$have="pay_rate>=".intval($_GET['rate']);
				$ser['rate']=intval($_GET['rate']);
			}
			
		}
		$this->assign('sea',$ser);		
		$gender =array(
			'1' => '男',
			'2' => '女'
		);
		
		$db_user = M('user');
		
		//分页
		$count = $db_user->alias('a')
					->field('count(b.TID) pay_rate')
					->join('LEFT JOIN zp_user_order b on b.user_id = a.TID and b.status>=2 '.$paytime)
					->group('a.TID')
					->where($where)->having($have)
					->select();
		$count=count($count);
		$this->page_set($count,15,'个用户',$ser);	
		
		$user_list = $db_user->alias('a')
						->field('a.TID,a.user_name,a.gender,a.user_phone,a.user_photo,a.first_pay_time,a.RegisterTime,a.user_score,count(b.TID) pay_rate')
						->join('LEFT JOIN zp_user_order b on b.user_id = a.TID and b.status>=2 '.$paytime)
						->group('a.TID')
						->where($where)->having($have)
						->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_user->getLastSql();die;
		foreach($user_list as $k=>$user){
			if(!empty($user['user_photo'])){
				$user_list[$k]['user_photo'] = '<img width="50px" src="'.$user['user_photo'].'"/>';
			}else{
				$user_list[$k]['user_photo'] = '无照片';
			}
			if(empty($user['area_name'])){
				$user_list[$k]['area_name'] = '无所属站点';
			}
			if($user['first_pay_time']=='0'){
				$user_list[$k]['first_pay_time']='暂无首单';
			}else{
				$user_list[$k]['first_pay_time']=date('Y-m-d H:i',$user['first_pay_time']);
			}
			$user_list[$k]['gender'] = $gender[$user['gender']];
		}
//		echo $db_user->getLastSql();
		$this->assign('gender',$gender);
		$this->assign('page',$this->page->show());
		$this->assign('user_list',$user_list);
		$this->display('User/list');
	}
	
//用户消息信息
	public function user_bill(){
		$UID = intval($_GET['UID']);
		$db_bill = M('user_bill');
//		$UID = 13;
		if($UID>0){
			//分页
			$count = $db_bill->alias('a')->where('a.user_id='.$UID)->count(0);
			
			$this->page_set($count,15,'条记录',array('UID'=>$UID));	
						
			$info = $db_bill->alias('a')
					->field('a.money,a.type,a.type,a.AddTime,c.show_id order_id,b.user_name')
					->join('LEFT JOIN zp_user b on a.user_id=b.TID LEFT JOIN zp_user_order c on a.order_id=c.TID')->where('a.user_id='.$UID)
					->limit($this->page->firstRow,$this->page->listRows)->select();
//			echo $db_bill->getLastSql();die;
			$this->assign('info',$info);
			$this->assign('page',$this->page->show());
			$this->display('User/bill');
		}else{
			$this->error('该用户信息不存在');
		}
	}
	
	//用户表格导出
	public function user_excel(){
		if(!empty($_GET['phone'])){
			$where['user_phone']=array('like','%'.trim($_GET['phone']).'%');
			$ser['phone'] = trim($_GET['phone']);
		}
		if($_GET['gender']!=='0'){
			$where['gender']=intval($_GET['gender']);
			$ser['gender']=intval($_GET['gender']);
		}
		list($start,$end)=explode('到',$_GET['time-range']);
		$ser['time-range']=trim($_GET['time-range']);
		if(!empty($start) && !empty($end)){
			$start=strtotime($start);
			$end=strtotime($end);
			$paytime = 'and b.PayTime BETWEEN '.$start.' AND '.$end;
//				$where['b.PayTime']=array('between',array($start,$end));
		}
		$have="pay_rate>=".intval($_GET['rate']);
		$ser['rate']=intval($_GET['rate']);	
		$this->assign('sea',$ser);
		$db_user=M('user');
		$list = $db_user->alias('a')
						->field('a.TID,a.user_name,a.gender,a.user_phone,a.user_photo,a.first_pay_time,a.RegisterTime,a.user_score,count(b.TID) pay_rate')
						->join('LEFT JOIN zp_user_order b on b.user_id = a.TID and b.status>=2 '.$paytime)
						->group('a.TID')
						->where($where)->having($have)->select();
						
//		echo $db_user->getLastSql();die;
		
		foreach($list as $k=>$user){
			$list[$k]['Register_time']=date('Y-m-d H:i',$user['Register_time']);
			if($user['first_pay_time']=='0'){
				$list[$k]['first_pay_time']='暂无首单';
			}else{
				$list[$k]['first_pay_time']=date('Y-m-d H:i',$user['first_pay_time']);
			}
			$list[$k]['gender'] = $gender[$user['gender']];
		}
		$gender =array(
			'0' => '未指定',
			'1' => '男',
			'2' => '女'
		);							
		vendor('PHPExcel.PHPExcel');
		// Create new PHPExcel object 创建excel类
		$objPHPExcel = new \PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		
		
		// Add some data
		//$objPHPExcel->setActiveSheetIndex(0);
		
		//excel表格从1开始计数,数组从0计数
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','用户名');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','性别');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','手机号');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','注册时间');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','首单时间');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','消费频率');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','空瓶数');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','销售信息');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','运费');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','备注');
		//合并单元格
//		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
		
//		//填充数据
		foreach($list as $key=>$ex_item){
			$num=$key+2;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $ex_item['user_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $ex_item['gender']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $ex_item['user_phone']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $ex_item['Register_time']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$num, $ex_item['first_pay_time']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$num, $ex_item['pay_rate']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$num, $ex_item['user_score']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $ex_item['product_info']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $ex_item['sell_info']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $ex_item['freight']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['remarks']);
		}
		
		// Rename worksheet(重命名表格)
		$objPHPExcel->getActiveSheet()->setTitle('用户信息');
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
//		dump($objPHPExcel);die;
		ob_end_clean();
		ob_start();		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="用户信息.'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;		
	}
	
	
	//发送优惠券设置
	public function coupon_user_send_edit(){
		$db_coupon=M('coupon');
		$list=$db_coupon->where('SendTime=5')->select();
		
		$gender =array(
			'1' => '男',
			'2' => '女'
		);		
		
		$this->assign('gender',$gender);
		$this->assign('coupon_list',$list);
		$this->display('User/coupon_send_edit');
	}
	
	//手工发送优惠券
	public function coupon_user_send_save(){
		$db_user=M('user');
		$db_user_coupon=M('user_coupon');
		$db_coupon=M('coupon');
		
		$info_coupon=$db_coupon->field('TID,TimeLimit')->find($_POST['coupon_id']);
		
		
		if(!empty($_POST['phone'])){
			$where['a.user_phone']=array('like','%'.trim($_POST['phone']).'%');
			$ser['phone'] = trim($_POST['phone']);
		}
		$gender=intval($_POST['gender']);
		if($gender!=0){
			$where['gender']=$gender;
			$ser['gender']=$gender;
		}
		list($start,$end)=explode('到',$_POST['time-range']);
		$ser['time-range']=trim($_POST['time-range']);
		if(!empty($start) && !empty($end)){
			$start=strtotime($start);
			$end=strtotime($end);
			$paytime = 'and b.PayTime BETWEEN '.$start.' AND '.$end;
//				$where['b.PayTime']=array('between',array($start,$end));
		}
		if(!empty($_POST['rate'])){
			$have="pay_rate>=".intval($_POST['rate']);
			$ser['rate']=intval($_POST['rate']);
		}	
		$list = $db_user->alias('a')
						->field('a.TID,count(b.TID) pay_rate')
						->join('LEFT JOIN zp_user_order b on b.user_id = a.TID and b.status>=2 '.$paytime)
						->group('a.TID')
						->where($where)->having($have)
						->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_user->getLastSql();die;
//		dump($list);
		
		
		foreach($list as $v){
			if($db_user_coupon->where('user_id='.$v['TID'].' and coupon_id='.$info_coupon['TID'])->count(0)>0){
				continue;
			}
			$info=array(
				'user_id'=>$v['TID'],
				'coupon_id'=>$info_coupon['TID'],
				'DeadTime'=>$_SERVER['REQUEST_TIME']+3600*24*$info_coupon['TimeLimit'],
				'AddTime'=>$_SERVER['REQUEST_TIME'],
				'status'    => 1
			);
			
			if($db_user_coupon->add($info)>0){
				continue;
			}else{
				$this->error('优惠券发送失败');
			}
		}
		$this->success('优惠券发送成功');
	}
//商品管理	
	
	//分类遍历获取 
	//$PID	父类id
	private function cate_ergodic($PID=0){
		$db_cate = M('category');
		$cate_list = $db_cate->field('TID,cate_name')->where('PID ='.$PID)->Order('OrderID')->select();
//		echo $db_cate->getLastSql();die;
		if(empty($cate_list)){
			return '';
		}else{			
			foreach($cate_list as $key => $cate){
				$cate_list[$key]['children'] = $this->cate_ergodic($cate['TID']);
			}
		}
		return $cate_list;
	}

	//分类遍历获取 
	//$PID	父类id
	private function cate_ergodic_v($PID=0){
		$db_cate = M('category');
		$cate_list = $db_cate->field('TID,cate_name')->where('PID <>0')->Order('PID,OrderID')->select();
//		echo $db_cate->getLastSql();die;
		if(empty($cate_list)){
			return '';
		}else{			
			return $cate_list;
		}
		return $cate_list;
	}

	

	//商品上架下架
	public function goods_onsale(){
		$db_goods = M('goods');
		$info = array(
			'TID' => intval($_GET['TID']),
			'is_onsale' => intval($_GET['is_onsale'])==1?1:0
		);
		if($info['TID']>0){			
			$db_goods->save($info);
			redirect($_SERVER['HTTP_REFERER']);die;
		}else{
			redirect($_SERVER['HTTP_REFERER']);die;
		}
	}
	
//bof抢购商品管理
	//活动商品列表
	public function ac_goods_list(){
		
		$db_goods=M('ac_goods');
		
		$list = $db_goods->alias(a)
					->field('a.TID,a.ac_title,a.ac_mark,a.status,a.promote_start_time,a.promote_end_time,a.promote_count,a.sold_count,b.seller_price,b.mark_price,c.title,d.area_name')
//					->field('a.ac_title,a.ac_mark,a.status,a.promote_start_time,a.promote_end_time,a.promote_count')
					->join('LEFT JOIN zp_area_goods b on a.area_store_goods_id=b.TID LEFT JOIN zp_goods_data c on b.goods_data_id=c.TID LEFT JOIN zp_area d on a.area_id=d.TID')
					->select();
					
//		echo $db_goods->getLastSql();die;			


		$this->assign('list',$list);
		$this->display('Ac/goods_list');
	}
	
	//活动商品编辑
	public function ac_goods_edit(){
		$db_area = M('area');
		$db_goods = M('ac_goods');
		$db_store_goods = M('area_goods');
		$area_list = $db_area->field('TID,area_name')->select();
		
		$TID = intval($_GET['TID']);
		if($TID>0){
			$info = $db_goods->alias('a')
						->field('a.TID,a.ac_title,a.ac_mark,a.area_id,a.status,a.promote_start_time,a.promote_end_time,a.promote_count,a.status,b.seller_price,b.mark_price,a.area_id,a.area_store_goods_id')
						->join('LEFT JOIN zp_area_goods b on a.area_store_goods_id=b.TID')
						->where('a.TID='.$TID)
						->find();
//			echo $db_goods->getLastSql();die;
			$info['promote_time']=date('Y-m-d',$info['promote_start_time']).' 到 '.date('Y-m-d',$info['promote_end_time']);
		}else{
			$info['promote_time']=date('Y-m-d',time()).' 到 '.date('Y-m-d',time());
		}
//		dump($info);die;
		$area_goods_list = $db_store_goods->alias('a')
					->field('a.TID,b.title')
					->join('zp_goods_data b on a.goods_data_id=b.TID')
					->where('b.type=4 and a.area_id='.$info['area_id'])
					->select();
		$this->assign('info',$info);
		$this->assign('area',$area_list);		
		$this->assign('store_goods_list',$area_goods_list);
		$this->assign('ac_status',C('AC_STATUS'));
		
		$this->display('Ac/goods_edit');
	}
	
	//活动商品保存
	public function ac_goods_save(){
//		dump($_POST);die;
		$db_ac = M('ac_goods');
		$db_sell_goods = M('area_goods');		
		$info_ac = array(
			'ac_title'=>trim($_POST['ac_title']),
			'ac_mark'=>trim($_POST['ac_mark']),
			'area_id'=>intval($_POST['area_id']),
			'area_store_goods_id'=>intval($_POST['area_store_goods_id']),
			'status'=>intval($_POST['status']),
			'promote_count'=>intval($_POST['promote_count']),
			'EditTime'=>$_SERVER['REQUEST_TIME']
		);
			
		//活动时间处理
		$promote_time = explode('到',trim($_POST['promote_time']));
		$info_ac['promote_start_time']=strtotime($promote_time[0]);
		$info_ac['promote_end_time']=strtotime($promote_time[1]);
			
		//排斥效应
		if($info_ac['status']==1){
			$db_ac->where('area_id='.$info_ac['area_id'])->setField('status','2');
//			echo $db_ac->getLastSql();die;
		}
					
		$TID = intval($_POST['TID']);		
		if($TID>0){
			$info_ac['TID'] = $TID;
			if($db_ac->save($info_ac)!==false){
				$info_goods=array(
							'TID'=>$info_ac['area_store_goods_id'],
							'mark_price'=>doubleval($_POST['mark_price']),
							'seller_price'=>doubleval($_POST['seller_price']),
							'EditTime'=>intval($_SERVER['REQUEST_TIME'])
				);
				if($db_sell_goods->save($info_goods)!==false){
					$this->success('保存活动信息成功',U('ac_goods_list'));
				}else{
					//echo $db_ac->getLastSql();die;
					$this->error('保存活动信息失败');
				}
			}else{
				//echo $db_ac->getLastSql();die;
				$this->error('保存活动信息失败');
			}
		}else{
			$info_ac['AddTime']=$_SERVER['REQUEST_TIME'];
			if($db_ac->add($info_ac)>0){
				$info_goods=array(
							'TID'=>$info_ac['area_store_goods_id'],
							'mark_price'=>doubleval($_POST['mark_price']),
							'seller_price'=>doubleval($_POST['seller_price']),
							'EditTime'=>intval($_SERVER['REQUEST_TIME'])
				);
				if($db_sell_goods->save($info_goods)!==false){
					$this->success('添加活动信息成功',U('ac_goods_list'));
				}else{
					$this->error('添加活动信息失败');
				}
			}else{
//				echo $db_ac->getLastSql();die;
				$this->error('添加活动信息失败');
			}
		}
	}
	
	
	//活动商品删除
	public function ac_goods_del(){
		$res = array(
		            'db' => array('name'=>'ac_goods'),
		            'file'=>array(''),
		            'edit'=>'',
		            'success'=>array('mes'=>'活动商品删除成功','url'=>'ac_goods_list'),
		            'error'=>array('mes'=>'活动商品删除失败','url'=>'')
		        );
		$this->db_del($res);
	}

	//活动商品设定结束
	public function ac_goods_attr_set(){
		$res = array(
					'TID'=>intval($_GET['TID']),
					'name'=>trim($_GET['name']),
					'value'=>intval($_GET['value'])
		);
		$db = M('ac_goods');
		$area_id = $db->where('TID='.$res['TID'])->getField('area_id');
		//排斥效应
		if($res['value']==1){
			$db->where('area_id='.$area_id)->setField('status','2');
//			echo $db_ac->getLastSql();die;
		}		
		$db->where('TID='.$res['TID'])->setField($res['name'],''.$res['value']);
//		echo $db->getLastSql();die;
		redirect($_SERVER['HTTP_REFERER']);die;
	}
	
	//ajax活动商品列表
	public function ajax_ac_goods_list(){
		$area_id = intval($_POST['area']);
		$db_area_goods = M('area_goods');
		$list = $db_area_goods->alias('a')
					->field('a.TID value,b.title,a.seller_price,a.mark_price')
					->join('zp_goods_data b on a.goods_data_id=b.TID')
					->where('b.type=4 and a.area_id='.$area_id)
					->select();
//		echo $db_area_goods->getLastSql();die;
		if(empty($list)){
			$list=array();
		}
		echo json_encode($list);
	}
	
	//活动商品上下架
	public function ac_goods_onsale(){
		$info = array(
					'TID'=>intval($_GET['TID']),
					'area_id'=>intval($_GET['area_id']),
					'is_onsale'=>intval($_GET['is_onsale'])==1?1:0
		);
//		dump($info);die;			
		$db_ac_goods = M('ac_goods');
		if($info['is_onsale']==1){
			$db_ac_goods->where('area_id='.$info['area_id'].' and is_onsale=1')->setField('is_onsale','0');
		}
		if($info['TID']>0){			
			$db_ac_goods->save($info);
//			echo $db_ac_goods->getLastSql();die;			
			redirect($_SERVER['HTTP_REFERER']);die;
		}else{
			redirect($_SERVER['HTTP_REFERER']);die;
		}
	}
	
	//商品库存
	public function ajax_area_store_list(){
		$AreaID = intval($_POST['area_id']);
		
		$db_store_goods = M('area_store_goods');
		$list = $db_store_goods->alias('a')
						->field('a.TID,b.Title,a.CostPrice,a.BoxCount,a.BottleCount,a.BoxCount-a.BoxSold BoxTotal,a.BottleCount-a.BottleSold BottleTotal,a.UnitBottle,a.BottleSold,a.BoxSold')
						->join('zp_store_goods b on a.StoreGoodsID=b.TID')
						->where('a.AreaID='.$AreaID)
						->select();
		
//		echo $db_store_goods->getLastSql();die;
		if(empty($list)){
			$list = array();
		}		
		echo json_encode($list);
	}
//eof抢购商品管理	

//商品分类管理
	public function cate_list(){
		$this->track['cate'][0] = 'class= "active open"';
		$this->track['cate']['list']='class = "active"';
		$this->assign('track',$this->track);		
		
		$db_cate = M('category');
		foreach($_POST['TID'] as $k=>$term){
			$info = array(
				'TID'     => intval($term),
				'OrderID' => intval($_POST['Order'][$k]));
			$db_cate->save($info);
		}
		
		
		//分页显示
		$count = $db_cate->count(0);

		$this->page_set($count,15,'种分类',array());
		
		$PID = intval($_GET['TID']);		
		$cate_list = $db_cate->field('TID,cate_name,PID,remark,is_hot,is_recommend,OrderID')->where('PID='.$PID)->order('OrderID')->limit($this->page->firstRow,$this->page->listRows)->select();
		if($PID==0){
			$parents =array(
				'cate_name' => '无父分类',
				'PID'       => 0,
				'TID'       => 0);
		}else{
			$parents = $db_cate->field('TID,PID,cate_name')->find($PID);
//			echo $db_cate->getLastSql();die;
		}
		foreach($cate_list as $key=>$value){
			if(empty($value['remark'])){
				$cate_list[$key]['remark']='无标签';
			}else{
				$cate_list[$key]['remark'] = '<img width="50px" src="'.$value['remark'].'"/>';
			}
			$cate_list[$key]['is_hot'] = $value['is_hot']==1?'是':'否';
			$cate_list[$key]['is_recommend'] = $value['is_recommend']==1?'是':'否';
		}
		$this->assign('page',$this->page->show());
		$this->assign('parent',$parents);
		$this->assign('cate_list',$cate_list);
		$this->display('Cate/list');
	}
	
	public function cate_tree($PID=0){
		$Indexed=trim(strval($_GET['Indexes']));
		//echo $Indexed;
		$Indexed_arr=array();
		if(!empty($Indexed)){
		$Indexed_arr=explode(',',$Indexed);
		}
				
		$db=M('category');	
		$list=$db->field('cate_name Title,OrderID,TID')->where('PID='.$PID)->order('OrderID asc ,TID asc')->select();
		$data_arr=array();
		foreach($list as $item){
			$data_arr[$item['TID']]=array(
				'text'=>$item['Title'].'-'.$item['OrderID'],
				'ID'=>$item['TID']
			);
			if(in_array($item['TID'],$Indexed_arr)){
				$data_arr[$item['Title']]['additionalParameters']['item-selected']=true;
			}
			if($db->where('PID='.$item['TID'])->count(0)<=0){
				$data_arr[$item['TID']]['type']='item';
			}else{
				$data_arr[$item['TID']]['type']='folder';		
				$data_arr[$item['TID']]['additionalParameters']['children']= $this->cate_tree($item['TID']);
			}
		}
		if($PID==0){
			print_r(json_encode($data_arr));
		}else{
			return $data_arr;
		}		
	}
	
	//分类排序
	public function cate_order(){
		$this->track['cate'][0] = 'class= "active open"';
		$this->track['cate']['list']='class = "active"';
		$this->assign('track',$this->track);	
				
		$db_cate = M('category');
		foreach($_POST['TID'] as $k=>$term){
			$info = array(
				'TID'     => intval($term),
				'OrderID' => intval($_POST['Order'][$k]));
			$db_cate->save($info);
		}
		$PID = intval($_POST['PID']);
		$cate_list = $db_cate->field('TID,cate_name,PID,remark,is_hot,is_recommend,OrderID')->where('PID='.$PID)->order('OrderID')->select();
//		echo $db_cate->getLastSql();die;
		if($PID==0){
			$parents =array(
				'cate_name' => '无父分类',
				'PID'       => 0,
				'TID'       => 0);
		}else{
			$parents = $db_cate->field('PID,TID,cate_name')->find($PID);
		}
		
		foreach($cate_list as $key=>$value){
			if(empty($value['remark'])){
				$cate_list[$key]['remark']='无标签';
			}else{
				$cate_list[$key]['remark'] = '<img width="50px" src="'.$value['remark'].'"/>';
			}
		}
		$this->assign('parent',$parents);
//		echo $db_cate->getLastSql();die;
		$this->assign('cate_list',$cate_list);
		$this->display('Cate/list');
	}
	
	public function cate_edit(){	
		
		$db_cate = M('category');
		if(isset($_GET['TID'])){
			$TID = intval($_GET['TID']);		
			$re_cate = $db_cate->field('TID,cate_name,PID,remark,OrderID,is_hot,is_recommend')->find($TID);			
			$this->assign('cate',$re_cate);
		}
		//获取父分类
		$parents_list = $db_cate->field('TID,cate_name')->where('PID=0')->order('OrderID')->select();
		$this->assign('parents',$parents_list);
		$this->display('Cate/edit');
	}
	
	public function cate_save(){
		$db_cate = M('category');
		
//		print_r($_POST);die;
		
		$info = array(
			'cate_name' => I('cate_name','','strval'),
			'PID'       => I('PID','','intval'),
			'OrderID'   => intval($_POST['order_id']),
			'is_hot'    => $_POST['is_hot']==1?1:0,
			'is_recommend'=> $_POST['is_recommend']==1?1:0);
		//标签上传
		$upload = $this->upload('Cate/');
//		print_r($upload);die;
		if(!empty($upload)){
			$info['remark'] = $this->rootpath.$upload[0]['savepath'].$upload[0]['savename'];		
		}
		
		$TID = I('TID','','intval');
		if($TID==0){
			$re_cate = $db_cate->add($info);
		}else{
			$info['TID'] = $TID;
			if(isset($info['remark'])){
				$del_load = $db_cate->field('remark')->find($TID);
				$this->file_del($del_load);
			}
			$re_cate = $db_cate->save($info);
		}
		if($re_cate !== false){
			$this->success('保存商品分类成功');
		}else{
			$this->error('保存商品分类失败');
		}
	}
	//标签删除
	public function cate_del(){
		$TID = intval($_GET['ID']);
		if($TID>0){
			$db_cate = M('category');
			if($db_cate->where('PID='.$TID)->count(0)>0){
				$this->error('该分类有子分类，请先移除子类再删除');die;
			}
			$del_load = $db_cate->field('remark')->find($TID);
			$this->file_del($del_load);
			if($db_cate->delete($TID)){
				$this->success('删除分类成功');
			}else{
				$this->error('删除分类失败');
			}
		}else{
			$this->success('删除成功');
		}
	}
	
	//设为热门
	public function cate_sethot(){
		$db_cate = M('category');
		$TID = intval($_GET['TID']);
		$is_hot = $_GET['is_hot']==1?1:0;
		$PID = $db_cate->field('PID')->find($TID);
		if($PID['PID']==0 && $is_hot==1){
			$this->error('顶级分类无法设为推荐');die;
		}		
		$db_cate->where('is_hot=1 and PID='.$PID['PID'])->setField('is_hot','0');
//		echo $db_cate->getLastSql();die;
		if($TID>0){
			if($db_cate->where('TID='.$TID)->setField(array('is_hot'=>$is_hot))!==false){
				$mes = $is_hot==1?'设为热门成功':'取消热门成功';
				$this->success($mes);die;
			}else{
				$mes = $is_hot==1?'设为热门失败':'取消热门失败';				
				$this->error($mes);die;
			}
		}else{
			$this->error('该分类不存在');
		}
	}
	
	//设为人气推荐
	public function cate_setrecommend(){
		$db_cate = M('category');
		$TID = intval($_GET['TID']);
		$is_recommend = $_GET['is_recommend']==1?1:0;
		$PID = $db_cate->field('PID')->find($TID);
		if($PID['PID']==0 && $is_recommend==1){
			$this->error('顶级分类无法设为推荐');die;
		}
		$db_cate->where('is_recommend=1 and PID='.$PID['PID'])->setField('is_recommend','0');
//		echo $db_cate->getLastSql();die;
		if($TID>0){
			if($db_cate->where('TID='.$TID)->setField(array('is_recommend'=>$is_recommend))!==false){
				$mes = $is_recommend==1?'设为推荐成功':'取消推荐成功';
				$this->success($mes);die;
			}else{
				$mes = $is_recommend==1?'设为推荐失败':'取消推荐失败';				
				$this->error($mes);die;
			}
		}else{
			$this->error('该分类不存在');
		}
	}
	
//App简介管理
	//显示简介
	public function about_edit(){
		$this->track['about'][0] = 'class= "active open"';
		$this->track['about']['edit']='class = "active"';
		$this->assign('track',$this->track);	
		
		$db_about =M('about');
		$re_about = $db_about->field('content')->find(1);
		$this->assign('about',$re_about['content']);
		$this->display('About/edit');
	}
	
	//保存简介
	public function about_save(){
		
		$db_about =M('about');
		$count = $db_about->where('TID=1')->count(0);
		$info = array('TID'    =>1,
				      'content'=> trim(strval($_POST['content'])));
		if($count==0){
			$re_about = $db_about->add($info);
		}else{
			$re_about = $db_about->save($info);
		}
		
		if($re_about){
			$this->success('保存APP介绍成功');
		}else{
			$this->error('保存APP介绍失败');
		}
	}
	
//空瓶使用说明
	//空瓶使用说明编辑
	public function score_use_edit(){
		$this->track['about'][0] = 'class= "active open"';
		$this->track['about']['score_use_edit']='class = "active"';
		$this->assign('track',$this->track);	
		
		$db_about =M('about');
		$re_about = $db_about->field('content')->find(2);
		$this->assign('about',$re_about['content']);
		$this->display('About/score_use_edit');
	}
	
	//空瓶使用说明保存
	public function score_use_save(){
//		if($this->roll_area['roll']!=2){
//			$this->error('您无权限编辑空瓶使用');die;
//		}		
		$db_about =M('about');
		$count = $db_about->where('TID=2')->count(0);
		$info = array('TID'    =>2,
				      'content'=> trim(strval($_POST['content'])));
		if($count==0){
			$re_about = $db_about->add($info);
		}else{
			$re_about = $db_about->save($info);
		}
		
		if($re_about){
			$this->success('保存使用说明成功');
		}else{
			$this->error('保存使用说明失败');
		}
	}


//站点管理
	//站点
	public function area_list(){			
		$db_area = M('area');
		//分页显示
		$count = $db_area->count(0);

		$this->page_set($count,15,'个站点',array());
				
		$re_area = $db_area->alias('a')->field('a.TID,a.area_name,a.area_detail,a.area_phone,a.isdefault,a.storehouse_addr,a.is_use,a.store_id,b.areaname area_province,c.areaname area_city,d.areaname area_area,e.NikeName,e.UserName')
					->join('LEFT JOIN zp_shop_area b on a.area_province = b.id')
					->join('LEFT JOIN zp_shop_area c on a.area_city = c.id')
					->join('LEFT JOIN zp_shop_area d on a.area_area = d.id')
					->join('LEFT JOIN zp_admin e on a.TID=e.AID')
					->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_area->getLastSql();die;
//		echo $show;die;
		$this->assign('page',$this->page->show());
		$this->assign('area',$re_area);
		$this->display('Area/list');
	}
	
	//编辑站点
	public function area_edit(){				
		$db_shop_area = M('shop_area');	
		$db_admin = M('admin');	
		if(isset($_GET['TID'])){
			$TID = intval($_GET['TID']);
			$db_area = M('area');
			$re_area = $db_area->field('TID,area_name,area_province,area_city,area_area,area_detail,area_phone,isdefault,is_use,storehouse_addr,store_id')->find($TID);
			$city_list = $db_shop_area->field('id,areaname')->where('parentid='.$re_area['area_province'])->select();
			$area_list = $db_shop_area->field('id,areaname')->where('parentid='.$re_area['area_city'])->select();
			
			$admin = $db_admin->field('TID,UserName,NikeName')->where('AID='.$TID)->find();
//			echo $db_admin->getLastSql();die;
			$this->assign('city_list',$city_list);
			$this->assign('admin',$admin);
			$this->assign('area_list',$area_list);
			$this->assign('area',$re_area);
		}
		
		
		//获取站点所在省
		$province_list = $db_shop_area->field('id,areaname')->where('parentid=0')->select();
		$this->assign('state_list',$province_list);
		
		
		$this->display('Area/edit');		
	}
	
	//保存站点
	public function area_save(){
		$db_area = M('area');
		$db_admin = M('admin');
		
//		print_r($_POST);die;

		if($_POST['Password']!=$_POST['rePassword']){
			$this->error('两次密码输入不一致');die;
		}
		
		$lng_lat = $this->lat_lng($_POST['city'],$_POST['area'],$_POST['storehouse_addr']);
		if(empty($lng_lat)){
			$this->error('无法获取经纬度信息');die;
		}else{
			$lng_lat = json_decode($lng_lat,true);
		}
		$info = array(
				'area_name'=>trim($_POST['area_name']),
				'latitude' =>$lng_lat['result']['location']['lat'],
				'longititude' => $lng_lat['result']['location']['lng'],      //经度
				'area_province'=> intval($_POST['province']),
				'area_city' => intval($_POST['city']),
				'area_area' => intval($_POST['area']),
				'area_detail'=>trim($_POST['detail']),
				'storehouse_addr'=>trim($_POST['storehouse_addr']),
				'area_phone' => trim($_POST['phone']),
				'isdefault' => $_POST['isdefault']==0?0:1,
				'is_use' => $_POST['is_use']==0?0:1,
				'EditTime' => $_SERVER['REQUEST_TIME'],
				'store_id'=>trim($_POST['store_id'])
		);
		
		$TID = intval($_POST['TID']);
		$AID = intval($_POST['AID']);
		if($TID>0){
			$info['TID']= $TID;
			if($db_area->save($info)!==false){
				$admin = array(
							'NikeName'=>trim($_POST['NikeName']),
							'UserName'=>trim($_POST['UserName']),
//							'Password'=>md5($_POST['Password']),
							'EditTime'=>$_SERVER['REQUEST_TIME'],
							'RoleID'=>8,
							'IsSys'=>1,
							'AID'=>$TID
				);
				if($AID>0){
					if(!empty($_POST['Password'])){
						$admin['Password']=md5($_POST['Password']);
					}
					$admin['TID']=$AID;
					if($db_admin->save($admin)!==false){
						$this->success('保存管理员信息成功',U('area_list'));die;
					}else{
						$this->error('保存管理员信息失败');die;
					}
				}else{
					$admin['RegTime']= $_SERVER['REQUEST_TIME'];
					$admin['Password']=md5($_POST['Password']);
					if($db_admin->add($admin)>0){
						$this->success('保存站点信息成功',U('area_list'));die;
					}else{
						$this->error('保存站点信息失败');die;
					}
				}
			}else{
				$this->error('站点保存失败');die;
			}
		}else{
			$info['AddTime']= $_SERVER['REQUEST_TIME'];
			$TID = $db_area->add($info);
			if($TID>0){
				$admin = array(
							'NikeName'=>trim($_POST['NikeName']),
							'UserName'=>trim($_POST['UserName']),
							'Password'=>md5($_POST['Password']),
							'EditTime'=> $_SERVER['REQUEST_TIME'],
							'RegTime' => $_SERVER['REQUEST_TIME'],
							'RoleID'=>8,
							'AID'=>$TID
							
				);
				if($db_admin->add($admin)>0){
					$this->success('保存站点信息成功',U('area_list'));die;
				}else{
					$this->error('保存站点信息失败');die;
				}
			}else{
				$this->error('站点添加失败');die;
			}
		}
	}
	
	//删除站点(相关站点信息是否删除)
	public function area_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_area = M('area');
			$db_admin = M('admin');
			if($db_area->delete($TID)!==false){
				if($db_admin->where('AID='.$TID)->delete()!==false){
					$this->success('删除成功');
				}else{
					$this->error('删除管理员失败');die;
				}
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('站点信息不存在');
		}		
	}
	
	//站点保存
	public function area_set(){
		$db_area = M('area');
		$key = trim($_GET['key']);
		if($key=='isdefault'){
			if($db_area->where('isdefault=1')->setField('isdefault','0')===false){
				$this->error('修改默认站点失败');die;
			}
		}
		$info = array(
					'TID' => intval($_GET['TID']),
					$key  => intval($_GET['value']),
					'EditTime'=>$_SERVER['REQUEST_TIME']
		);
		$db_area->save($info);
		redirect($_SERVER['HTTP_REFERER']);die;
	}
	
	//获取经纬度
	/*
	*@ $city		市
	*@ $area		区
	*@ $detail	    街道地址
	*@ 返回值	   	经纬度等信息
	*/
/*	
	private function lat_lng($city,$area,$detail){
		//经纬度后台获取
		$db_shop_arae = M('shop_area');
		$re_city = $db_shop_arae->field('areaname')->find($city);
		$re_area = $db_shop_arae->field('areaname')->find($area);
		$url = 'http://api.map.baidu.com/geocoder/v2/?address='.$re_city['areaname'].$re_area['areaname'].$detail.'&city='.$re_city['areaname'].'&output=json&ak=x6bzMS9V8DVxA7uOMWqepfQHxXZR2KOQ';	
//		echo $url;die;
		return $this->curl_makces($url);
	}
*/
//bof 站点商品管理
	public function area_goods_list(){
		if(!empty($_GET['creat_table'])){
			$this->area_goods_excel();
		}
//		dump($_GET);die;
		
		if(!empty($_GET['search'])){
			$ser['search']=1;
//			if(!empty($_GET['show_id'])){
//				$where['a.TID']=array('like','%'.trim($_GET['show_id']).'%');
//				$ser['show_id'] = trim($_GET['show_id']);
//			}
			if(!empty($_GET['title'])){
				$where['b.title']=array('like','%'.trim($_GET['title']).'%');
				$ser['title'] = trim($_GET['title']);
			}
			$ser['type']=$_GET['type'];
			if($_GET['type']!='all'){
				$where['b.type']=intval($_GET['type']);
				$ser['type']=$where['b.type'];
			}
		
			$min=intval($_GET['store_min']);
			$max=intval($_GET['store_max']);
			if(!($min==0 && $max==0)){
				$min=min($min,$max);
				$max=max($min,$max);
				$where['a.store_count']=array('between',array($min,$max));
				$ser['store_min']=$min;
				$ser['store_max']=$max;
			}
						
//			list($start,$end)=explode('到',$_GET['time-range']);
//			$ser['time-range']=trim($_GET['time-range']);
//			if(!empty($start) && !empty($end)){
//				$start=strtotime($start);
//				$end=strtotime($end);
//				$where['a.PayTime']=array('between',array($start,$end));
//			}
			if(!empty($_GET['area_id'])){
				$where['a.area_id']=intval($_GET['area_id']);
				$ser['area_id']=intval($_GET['area_id']);
			}
//			$UID =intval($_GET['UID']);
//			if($UID>0){
//				$where['a.user_id']=$UID;
//				$ser['UID']=$UID;
//			}
		}
		
		$this->assign('s',$ser);
		$this->assign('type',C('GOODS_TYPE'));
		$this->area_sel();
		
		$res = array(
		            'db' => array('name'=>'area_goods',
		                         'field'=>'a.TID,a.store_count,a.sold_count,a.mark_price,a.seller_price,a.is_onsale,a.cart_re,a.order_id,b.title,b.logo,c.type,d.area_name',
		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID LEFT JOIN zp_area d on a.area_id=d.TID',
		                        'order'=>'a.order_id asc',
		                        'where'=>$where),
		            'page'=>$ser,
		            'display' => 'AreaGoods/list'
		);
		$this->db_list($res);
	}

	public function area_goods_eva_list(){
		$goods_id=intval($_GET['GoodsID']);
		if($goods_id<0){
			$this->error('请选择商品');die;
		}
		$where['a.goods_id']=$goods_id;
		$info['GoodsID']=$goods_id;
		
		$db_goods=M('area_goods');
		
		$goods_list=$db_goods->alias('a')
						->field('a.TID GoodsID,c.title,b.area_name')
						->join('LEFT JOIN zp_area b on a.area_id=b.TID LEFT JOIN zp_goods_data c on a.goods_data_id=c.TID')
						->select();
		$this->assign('goods_list',$goods_list);
		$this->assign('info',$info);
		$res = array(
		            'db' => array('name'=>'order_recomment',
		                         'field'=>'a.TID,a.content,a.images,a.AddTime,a.score,b.user_name,c.show_id,e.title,f.area_name',
		                        'join'=>'LEFT JOIN zp_user b on a.user_id=b.TID 
		                        		 LEFT JOIN zp_user_order c on a.order_id=c.TID 
		                        		 LEFT JOIN zp_area_goods d on a.goods_id=d.TID 
		                        		 LEFT JOIN zp_goods_data e on d.goods_data_id=e.TID
		                        		 LEFT JOIN zp_area f on d.area_id=f.TID',
		                        'order'=>'a.AddTime',
		                        'where'=>$where),
		            'page'=>$info,
		            'display' => 'AreaGoods/eva_list'
		    );
		if(!empty($res['db']['where'])){
			$where=$res['db']['where'];			
		}
		$db = M($res['db']['name']);
		$count = $db->alias('a')->where($where)->count(0);	
		$this->page_set($count,15,'种分类',$res['page']);
//		echo $db->getLastSql();die;			
		$info = $db->alias('a')
					->field($res['db']['field'])->where($where)
					->join($res['db']['join'])
					->limit($this->page->firstRow,$this->page->listRows)
					->order($res['db']['order'])->select();
//		echo $db->getLastSql();die;
		//附加配置文件
		foreach($info as $k=>$v){
			if(!empty($v['images'])){
				$im=explode('||',$v['images']);
				$info[$k]['images']=$im;
			}
		}
//		dump($info);die;
		$this->assign('list',$info);
		$this->assign('page',$this->page->show());				
		$this->display($res['display']);		
	}

	public function area_goods_eva_del(){
		$db=M('order_recomment');
		$TID=intval($_GET['TID']);
		if($TID>0){
			$images=$db->where('TID='.$TID)->getField('images');
			$this->file_del($images);
			if($db->delete($TID)!==false){
				$this->success('删除成功',U('area_goods_eva_list',array('GoodsID'=>$_GET['GoodsID'])));
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('删除失败');
		}
	}
	
	public function area_goods_edit(){
		$res = array(
		            'db' => array('name'=>'area_goods',
		                         'field'=>'a.TID,a.mark_price,a.seller_price,a.is_onsale,a.cart_re,a.order_id,b.title,c.type,d.area_name',
		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID LEFT JOIN zp_area d on a.area_id=d.TID',
		                        'order'=>'',
		                        'where'=>''),
		            'display' => 'AreaGoods/edit'
		    );
		    $this->db_edit($res);
	}

	public function area_goods_save(){
		$info=array(
				'TID'=>intval($_POST['TID']),
				'seller_price'=>doubleval($_POST['seller_price']),
				'mark_price'=>doubleval($_POST['mark_price']),
				'is_onsale'=>intval($_POST['is_onsale'])==1?1:0,
				'cart_re'=>intval($_POST['cart_re'])==1?1:0,
				'order_id'=>intval($_POST['order_id']),
				'EditTime'=>$_SERVER['REQUEST_TIME']
		);
		$db=M('area_goods');
		if($db->save($info)!==fales){
			$this->success('设置商品信息成功',U('area_goods_list'));
		}else{
			$this->error('设置商品信息失败');
		}
	}
	
	public function list_area_goods_attr_set(){
//		dump($_POST);die;
		$db=M('area_goods');
		$tid_list = implode(',',$_POST['TID']);
		
		if($_POST['on_sale']){
			$db->where('TID in ('.$tid_list.')')->setField('is_onsale','1');
		}else{
			$db->where('TID in ('.$tid_list.')')->setField('is_onsale','0');
		}
		redirect($_SERVER['HTTP_REFERER']);die;
	}
		
	public function area_goods_attr_set(){
		$res = array(
					'db'=>'area_goods',
					'TID'=>intval($_GET['TID']),
					'name'=>trim($_GET['name']),
					'value'=>intval($_GET['value'])
		);
		$this->db_set($res);
	}
	
	private function area_goods_excel(){
		
		if(!empty($_GET['search'])){
			$ser['search']=1;
//			if(!empty($_GET['show_id'])){
//				$where['a.TID']=array('like','%'.trim($_GET['show_id']).'%');
//				$ser['show_id'] = trim($_GET['show_id']);
//			}
			if(!empty($_GET['title'])){
				$where['b.title']=array('like','%'.trim($_GET['title']).'%');
				$ser['title'] = trim($_GET['title']);
			}
			$ser['type']=$_GET['type'];
			if($_GET['type']!='all'){
				$where['b.type']=intval($_GET['type']);
			}
						
//			list($start,$end)=explode('到',$_GET['time-range']);
//			$ser['time-range']=trim($_GET['time-range']);
//			if(!empty($start) && !empty($end)){
//				$start=strtotime($start);
//				$end=strtotime($end);
//				$where['a.PayTime']=array('between',array($start,$end));
//			}
			if(!empty($_GET['area_id'])){
				$where['a.area_id']=intval($_GET['area_id']);
				$ser['area_id']=intval($_GET['area_id']);
			}
//			$UID =intval($_GET['UID']);
//			if($UID>0){
//				$where['a.user_id']=$UID;
//				$ser['UID']=$UID;
//			}
		}		
		
//		$res = array(
//		            'db' => array('name'=>'area_goods',
//		                         'field'=>'a.TID,a.store_count,a.sold_count,a.mark_price,a.seller_price,a.is_onsale,a.cart_re,a.order_id,b.title,b.logo,c.type,d.area_name',
//		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID LEFT JOIN zp_area d on a.area_id=d.TID',
//		                        'order'=>'a.order_id asc',
//		                        'where'=>$where),		
		
		$db_goods=M('area_goods');
		$db_cate = M('category');
		$list = $db_goods->alias('a')
					->field('a.TID,a.store_count,a.sold_count,a.mark_price,a.seller_price,a.is_onsale,a.cart_re,a.order_id,b.title,b.mark,b.cate,c.type,d.area_name')
					->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID LEFT JOIN zp_area d on a.area_id=d.TID')
					->where($where)->select();
					
		foreach($list as $k=>$v){
			$ca='';
			
			$cate = $db_cate->field('cate_name cate')->where('TID in ('.$v['cate'].')')->select();
			foreach($cate as $va){
				if(empty($ca)){
					$ca.=$va['cate'];
				}else{
					$ca.='、'.$va['cate'];
				}
			}
			$list[$k]['cate']=$ca;
			$list[$k]['is_onsale']=$v['is_onsale']==0?'否':'是';
		}
		
//		dump($list);die;
		vendor('PHPExcel.PHPExcel');
		// Create new PHPExcel object 创建excel类
		$objPHPExcel = new \PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		// Add some data
		//$objPHPExcel->setActiveSheetIndex(0);
		
		//excel表格从1开始计数,数组从0计数
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','商品名称');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','补充说明');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','分类');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','销售类型');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','市场价');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','购买价');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','库存数');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','销售量');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','上架');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','所在站点');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','备注');
		
		//合并单元格
//		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
		
//		//填充数据
		foreach($list as $key=>$ex_item){
			$num=$key+2;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $ex_item['title']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $ex_item['mark']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $ex_item['cate']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $ex_item['type']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$num, $ex_item['mark_price']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$num, $ex_item['seller_price']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$num, $ex_item['store_count']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $ex_item['sold_count']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $ex_item['is_onsale']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $ex_item['area_name']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['remarks']);
		}
		
		// Rename worksheet(重命名表格)
		$objPHPExcel->getActiveSheet()->setTitle('站点商品列表');
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="站点商品列表.'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;				
	}
//eof 站点商品管理
//bof 总部库存管理
	public function head_store_list(){
		$this->assign('type',C('GOODS_TYPE'));
		if(!empty($_GET['creat_table'])){
			$this->head_store_excel();
		}
		if(!empty($_GET['search'])){
			if(!empty($_GET['title'])){
				$where['b.title']=array('like','%'.trim($_GET['title']).'%');
				$info['title']=trim($_GET['title']);
			}
			if($_GET['type']!='all'){
				$where['b.type']=intval($_GET['type']);
				$info['type']=$where['b.type'];
			}
			$info['search']=1;
		}
		$this->assign('info',$info);
		$res = array(
		            'db' => array('name'=>'head_store',
		                         'field'=>'a.TID,b.title,c.type,a.store_count,a.avg_cost_price,a.AddTime,a.EditTime',
		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID',
		                        'order'=>'a.AddTime desc',
		                        'where'=>$where),
		            'page'=>$info,
		            'display' => 'HeadStore/list'
		);
		$this->db_list($res);
	}
	
	//库存日志添加
	public function head_store_log_list_add(){
		$db_goods_data = M('goods_data');
		$db_sup = M('supplier');
		$db_area = M('area');
		
		$area_list = $db_area->field('TID,area_name title')->select();
		$sup_list = $db_sup->field('TID,Company')->select();
		$goods_list=$db_goods_data
						->alias('a')
						->field('a.TID,a.title,b.type')
						->join('LEFT JOIN zp_seller_type b on a.type=b.TID')
						->select();
		$this->assign('area_list',$area_list);
		$this->assign('sup_list',$sup_list);
		$this->assign('goods_list',$goods_list);
		$this->display('HeadStore/log_add');				
	}
	
	public function head_store_log_list_save(){
//		dump($_POST);die;
		$db_head_store_log=M('head_store_log');
		$db_area_store_log=M('area_store_log');
		$db_head_store=M('head_store');
		$db_area_store=M('area_goods');
		$db_goods = M('goods_data');
		
		foreach($_POST['action'] as $k=>$v){
			//判断数量,数量为0跳过
			$count=intval($_POST['count'][$k]);
			if($count==0){
				continue;
			}
			$info=array(
				'action'=>intval($_POST['action'][$k]),
				'goods_data_id'=>intval($_POST['goods_data'][$k]),
				'count'=>$count,
				'cost_price'=>doubleval($_POST['cost_price'][$k]),
				'about_id'=>intval($_POST['about'][$k]),
				'act_person'=>trim($_POST['act_person'][$k]),
				'act_time'=>strtotime($_POST['act_time'][$k]),
				'AddTime'=>$_SERVER['REQUEST_TIME'],
				'EditTime'=>$_SERVER['REQUEST_TIME']
			);
			//添加记录
			$head_log_tid =$db_head_store_log->add($info); 
//			echo $db_head_store_log->getLastSql();die;
			if($head_log_tid>0){
				$old_head_info = $db_head_store->field('TID,goods_data_id,store_count,avg_cost_price')
									->where('goods_data_id='.$info['goods_data_id'])
									->find();
				//对应进行相关操作
				switch($info['action']){
					case 1: //供应商进货
						if(!empty($old_head_info)){
							$head_info = array(
											'TID'=>$old_head_info['TID'],
											'store_count'=>$info['count']+$old_head_info['store_count'],
											'avg_cost_price'=>($old_head_info['store_count']*$old_head_info['avg_cost_price']+$info['count']*$info['cost_price'])/($old_head_info['store_count']+$info['count']),
											'EditTime'=>$_SERVER['REQUEST_TIME']
							);
							if($db_head_store->save($head_info)!==false){
								countinue;
							}else{
								$db_head_store_log->delete($head_log_tid);
								$this->error('修改总仓库存失败');die;
							}
						}else{
							$head_info = array(
											'goods_data_id'=>$info['goods_data_id'],											
											'store_count'=>$info['count'],
											'avg_cost_price'=>$info['cost_price'],
											'AddTime'=>$_SERVER['REQUEST_TIME'],
											'EditTime'=>$_SERVER['REQUEST_TIME']
							);
							if($db_head_store->add($head_info)>0){
								continue;
							}else{
								$db_head_store_log->delete($head_log_tid);
								$this->error('添加总仓库存失败');die;
							}
						}
						break;
					case 2: //退货到供应商
					
						break;
					case 3: //下发到分站
//				'action'=>intval($_POST['action'][$k]),
//				'goods_data_id'=>intval($_POST['goods_data'][$k]),
//				'count'=>$count,
//				'cost_price'=>doubleval($_POST['cost_price'][$k]),
//				'about_id'=>intval($_POST['about'][$k]),
//				'act_person'=>trim($_POST['act_person'][$k]),
//				'act_time'=>strtotime($_POST['act_time'][$k]),						
						$area_log_info=array(
							'area_id'=>$info['about_id'],
							'goods_data_id'=>$info['goods_data_id'],
							'action'=>$info['action'],
							'act_person'=>$info['act_person'],
							'act_time'=>$info['act_time'],
							'count'=>$info['count'],
							'AddTime'=>$_SERVER['REQUEST_TIME'],
							'EditTime'=>$_SERVER['REQUEST_TIME']
						);
						$area_log_tid=$db_area_store_log->add($area_log_info);
						if($area_log_tid>0){
							//检查分站是否有相应库存信息
							$old_area_info = $db_area_store->field('TID,goods_data_id,store_count')
												->where('area_id='.$info['about_id'].' and goods_data_id='.$info['goods_data_id'])
												->find();
//							echo $db_area_store->getLastSql();die;
							//检查库存数量是否足够
							$store_count = $db_head_store->field('store_count')
												->where('goods_data_id='.$info['goods_data_id'])
												->find();
							if(intval($store_count['store_count'])<$info['count']){
								$db_head_store_log->delete($head_log_tid);
								$db_area_store_log->delete($area_log_tid);
								$this->error('总仓库存不足');die;
							}
							if(!empty($old_area_info)){
								//总仓数量减少
								if($db_head_store->where('goods_data_id='.$info['goods_data_id'])->setDec('store_count',$info['count'])==false){
									$db_head_store_log->delete($head_log_tid);
									$db_area_store_log->delete($area_log_tid);
									$this->error('总仓库存数量减少失败');die;
								}
								//站点库存增加
								if($db_area_store->where('TID='.$old_area_info['TID'])->setInc('store_count',$info['count'])==false){
									//总仓库存恢复
									$db_head_store->where('goods_data_id='.$info['goods_data_id'])->setInc('store_count',$info['count']);
									$db_head_store_log->delete($head_log_tid);
									$db_area_store_log->delete($area_log_tid);
									$this->error('分站库存数量增加失败');die;
								}
							}else{
								//总仓数量减少
								if($db_head_store->where('goods_data_id='.$info['goods_data_id'])->setDec('store_count',$info['count'])==false){
									$db_head_store_log->delete($head_log_tid);
									$db_area_store_log->delete($area_log_tid);
									$this->error('总仓库存数量减少失败');die;
								}
								$price_info=$db_goods->field('def_seller_price,def_mark_price')->find($info['goods_data_id']);
								$area_info=array(
									'area_id'=>$info['about_id'],
									'goods_data_id'=>$info['goods_data_id'],
									'store_count'=>$info['count'],
									'seller_price'=>$price_info['def_seller_price'],
									'mark_price'=>$price_info['def_mark_price'],
									'is_onsale'=>1,
									'AddTime'=>$_SERVER['REQUEST_TIME'],
									'EditTime'=>$_SERVER['REQUEST_TIME']
								);
								$area_store_tid =$db_area_store->add($area_info); 
								if($area_store_tid>0){
									continue;
								}else{
									//总仓库存恢复
									$db_head_store->where('goods_data_id='.$info['goods_data_id'])->setInc('store_count',$info['count']);
									$db_head_store_log->delete($head_log_tid);
									$db_area_store_log->delete($area_log_tid);
									$this->error('分站库存数量增加失败');die;
								}
							}
						}else{
							$db_head_store_log->delete($head_log_tid);
							$this->error('分站库存记录添加失败');die;
						}
						
						break;
					case 4: //卖出
						break;
					case 5: //退到总仓库
						break;
				}
			}
		}
		$this->success('批量处理成功',U('head_store_list'));die;
		
	}
	private function head_store_excel(){
//		$res = array(
//		            'db' => array('name'=>'head_store',
//		                         'field'=>'a.TID,b.title,c.type,a.store_count,a.avg_cost_price,a.AddTime,a.EditTime',
//		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID',
//		                        'order'=>'a.AddTime desc',
//		                        'where'=>$where),
//		            'page'=>$info,
//		            'display' => 'HeadStore/list'
//		);
//		$this->db_list($res);
				
		if(!empty($_GET['title'])){
			$where['b.title']=array('like','%'.trim($_GET['title']).'%');
			$info['title']=trim($_GET['title']);
		}
		if($_GET['type']!='all'){
			$where['b.type']=intval($_GET['type']);
			$info['type']=$where['b.type'];
		}		
		
//		$res = array(
//		            'db' => array('name'=>'area_goods',
//		                         'field'=>'a.TID,a.store_count,a.sold_count,a.mark_price,a.seller_price,a.is_onsale,a.cart_re,a.order_id,b.title,b.logo,c.type,d.area_name',
//		                        'join'=>'LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID LEFT JOIN zp_area d on a.area_id=d.TID',
//		                        'order'=>'a.order_id asc',
//		                        'where'=>$where),		
		
		$db_goods=M('head_store');
		$db_cate = M('category');
		$list = $db_goods->alias('a')
					->field('a.TID,b.title,c.type,a.store_count,a.avg_cost_price,a.AddTime,a.EditTime')
					->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
					->where($where)->select();
//		dump($list);die;
		vendor('PHPExcel.PHPExcel');
		// Create new PHPExcel object 创建excel类
		$objPHPExcel = new \PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		// Add some data
		//$objPHPExcel->setActiveSheetIndex(0);
		
		//excel表格从1开始计数,数组从0计数
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','商品名称');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','商品类型');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','库存数');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','成本均价');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','备注');
		
		//合并单元格
//		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
		
//		//填充数据
		foreach($list as $key=>$ex_item){
			$num=$key+2;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $ex_item['title']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $ex_item['type']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $ex_item['store_count']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $ex_item['avg_cost_price']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['remarks']);
		}
		
		// Rename worksheet(重命名表格)
		$objPHPExcel->getActiveSheet()->setTitle('总部商品库存列表');
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="总部商品列表.'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;				
	}
//eof 总部库存管理	
//工作人员管理
	public function worker_list(){			
		
		$status =array(
			'0' => '试用期',
			'1' => '正式员工',
			'2' => '解雇');	
		$db_admin = M('worker');
		
		if(!empty($_GET['search'])){
			if(!empty($_GET['name'])){
				$where['a.NickName']=array('like','%'.trim($_GET['name']).'%');
				$page['name']=trim($_GET['name']);
			}
			if($_GET['AreaID']!=0){
				$where['a.AreaID']=intval($_GET['AreaID']);
				$page['AreaID']=$where['a.AreaID'];
			}
			$page['search']=1;
		}
		
		$this->assign('info',$page);
		//分页显示
		$count = $db_admin->alias('a')->where($where)->count(0);
		
		$this->page_set($count,15,'个员工',$page);
		
		$where['a.Del']=0;
		$admin_list = $db_admin->alias('a')->field('b.area_name,a.TID,a.NickName,a.Photo,a.Phone,a.Age,a.CardID,a.Addr,a.WorkTime,a.BasePay,a.Status')
							   ->join('LEFT JOIN zp_area b on a.AreaID = b.TID')
							   ->where($where)
							   ->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_admin->getLastSql();die;
//		dump($admin_list);die;
		$this->assign('page',$this->page->show());
		$this->assign('admin_list',$admin_list);
		$this->area_sel();
		$this->display('Worker/list');
	}
	
	public function worker_order_list(){
		$where['a.status']=array('in',array('4,5'));
		if(!empty($_GET['search'])){
			$ser['search']=1;
//			if($_GET['status']!='all'){
//				$where['a.status']=intval($_GET['status']);
//				$ser['status']=$where['a.status'];
//			}
						
			list($start,$end)=explode('到',$_GET['time-range']);
			$ser['time-range']=trim($_GET['time-range']);
			if(!empty($start) && !empty($end)){
				$start=strtotime($start);
				$end=strtotime($end);
				$where['a.PayTime']=array('between',array($start,$end));
			}
			if(!empty($_GET['worker_id'])){
				$where['a.worker_id']=intval($_GET['worker_id']);
				$ser['worker_id']=intval($_GET['worker_id']);
			}else{
				$where['a.worker_id']=array('neq','0');
			}
		}	
		$this->assign('search',$ser);
				
		$db_worker=M('worker');
		$db_order = M('user_order');
		$db_goods_order = M('user_order_goods');
		
		$worker_list=$db_worker->alias('a')
						->field('a.TID,a.NickName,b.area_name area')
						->join('zp_area b on a.AreaID=b.TID')
						->select();
//		echo $db_worker->getLastSql();die;
		$count = $db_order->alias('a')->where($where)->count(0);
		//配送提成
		$per=$db_order->alias('a')->where($where)->sum('worker_percentage');
		
		$this->page_set($count,15,'个订单',$ser);	
		$order_list = $db_order->alias('a')
							   ->field('a.TID,a.show_id,a.AddTime,a.AppointmentTime,a.SendTime,a.status,a.Actually_pay,a.Total_price,a.freightage_pay,a.score_used,a.worker_percentage,b.user_name,c.area_name,d.NickName')
							   ->join('LEFT JOIN zp_user b on a.user_id=b.TID LEFT JOIN zp_area c on a.area_id=c.TID LEFT JOIN zp_worker d on a.worker_id=d.TID')
							   ->where($where)->limit($this->page->firstRow,$this->page->listRows)->order('a.AddTime desc')->select();
							   
//		echo $db_order->getLastSql();die;
		
		foreach($order_list as $key=>$order){
			$order_list[$key]['status'] = $status[$order['status']];
			
			$order_list[$key]['goods'] = $db_goods_order->field('goods_name,goods_small,price,type,number')->where('order_id='.$order['TID'])->select();
			$order_list[$key]['goods_count']=count($order_list[$key]['goods'])+1;
			foreach($order_list[$key]['goods'] as $k=>$goods){
				if(empty($goods['goods_small'])){
					$order_list[$key]['goods'][$k]['goods_small']='暂无图片';
				}else{
					$order_list[$key]['goods'][$k]['goods_small'] = '<img src="'.$goods['goods_small'].'" width="60px"/>';
				}
			}
			if($order['SendTime']>0){
				$order_list[$key]['SendTime'] = date('Y-m-d G:i',$order['SendTime']);
			}else if($order['SendTime']=='0'){
				$order_list[$key]['SendTime'] = '尽快配送';
			}else{
				$order_list[$key]['SendTime'] = '<span class="red">加急配送</span>';
			}
		}
//		dump($order_list);die;
		$this->assign('per',$per);
		$this->assign('worker_list',$worker_list);
		$this->assign('status',$status);
		$this->assign('page',$this->page->show());		
		$this->assign('order_list',$order_list);
		$this->display('Worker/order_list');		
	}
	
	private function area_sel(){
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
		$this->assign('area',$area_list);
	}
	
	public function worker_edit(){		
		
		$roll = array(
			'0' => '配送员',
			'1' => '站点管理员',
			'2' => '超级管理员');
			
		$status =array(
			'0' => '试用期',
			'1' => '正式员工',
			'2' => '解雇');	
					
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_admin = M('worker');
			$re_admin = $db_admin->field('TID,NickName,Photo,Phone,Age,CardID,Addr,WeChat,QQ,WorkTime,BasePay,Status,AreaID')->find($TID);

		}else{
			$re_admin['WorkTime']=$_SERVER['REQUEST_TIME'];
		}
		
		$this->assign('admin',$re_admin);
		//显示站点			
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
		$this->assign('area',$area_list);			
		//权限
		$this->assign('roll',$roll);
		//状态
		$this->assign('status',$status);
		
		$this->display('Worker/edit');
	}
	
	//保存员工信息(权限和基本信息)
	public function worker_save(){
		
//		dump($_POST);die;
		$info = array(
			'NickName' => trim(strval($_POST['NickName'])),
			'Age'      => intval(strval($_POST['Age'])),
			'CardID'   => trim($_POST['CardID']),
			'Phone'    => intval($_POST['Phone']),
			'Addr'     => trim($_POST['Addr']),
			'AreaID'   => intval($_POST['AreaID']),
			'WeChat'   => trim($_POST['WeChat']),
			'QQ'       => trim($_POST['QQ']),
			'WorkTime' => strtotime($_POST['WorkTime']),
			'BasePay'  => floatval($_POST['BasePay']),
			'Status'   => intval($_POST['status'])
			);
					  
		$photo = $this->upload('Admin/');
		if(!empty($photo)){
			$info['Photo'] = $this->rootpath.$photo[0]['savepath'].$photo[0]['savename'];		
		}
		
		if(empty($info['Phone'])){
			$this->error('工作人员电话为空');die;
		}
		
		if(empty($info['BasePay'])){
			$this->error('未设定工作人员底薪');die;
		}
		
		$db_admin = M('worker');
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			$old_admin = $db_admin->field('Photo')->find($TID);
			if(!empty($info['Photo'])){
				$this->file_del($old_admin['Photo']);
			}

			if($db_admin->save($info)!==false){
				$this->success('员工信息保存成功',U('Home/worker_list'));die;
			}else{
				//echo $db_admin->getLastSql();die;
				$this->error('员工信息保存失败');die;
			}
		}else{
			$info['AddTime'] = $_SERVER['REQUEST_TIME'];
			$re_ad = $db_admin->add($info);
			if($re_ad){
				$this->success('员工信息保存成功',U('Home/worker_list'));die;
			}else{
				$this->error('员工信息保存失败');die;
			}
		}
	}
	//删除工作人员
	public function worker_del(){
		$res = array(
		            'db' => array('name'=>'worker'),
		            'file'=>array('Photo'),
		            'edit'=>'',
		            'success'=>array('mes'=>'删除员工成功','url'=>'worker_list'),
		            'error'=>array('mes'=>'删除员工信息失败','url'=>'')
		        );
		$TID = intval($_GET['TID']);
		$db_worker=M('worker');
		if($TID>0){
			if($db_worker->where('TID='.$TID)->setField('Del','1')!==false){
				$this->success('删除员工信息成功',U('worker_list'));
			}else{
				$this->error('删除员工信息成功',U('worker_list'));
			}
			
			$this->db_del($res);
		}
	}
	//解雇工作人员
	public function worker_fire(){
		$TID = intval($_GET['TID']);
		$UID = strval($_COOKIE['admin']);
		if($TID == $_SESSION[$UID]){
			$this->error('对自己进行解雇操作');die;
		}
		if($TID>0){
			$db_admin = M('admin');
			$info = array(
				'TID'=>$TID,
				'status'=>2);
			if($db_admin->save($info)!==false){
				M('roll_node')->where('admin_id='.$TID)->delete();
				$this->success('员工已解雇');die;
			}else{
				$this->error('员工未解雇');die;
			}
		}else{
			$this->error('该员工信息不存在');die;
		}
	}
	
//bof 供应商管理

	public function suppliers_list(){
		$db = M('supplier');
		//分页显示
		$count = $db->count(0);
		
		$this->page_set($count,15,'个供应商',array());							   
		$info = $db->field('TID,Company,Contacts,Phone,Addr,AddTime,EditTime')
				   ->limit($this->page->firstRow,$this->page->listRows)->select();		
		$this->assign('info',$info);
		$this->assign('page',$this->page->show());
		$this->display('Supplier/list');
	}
	
	public function suppliers_edit(){
		$db = M('supplier');
		$TID = intval($_GET['TID']);
		if($TID>0){
			$info = $db->field('TID,Company,Contacts,Phone,Addr')->find($TID);
		}
		$this->assign('info',$info);
		$this->display('Supplier/edit');
	}
	
	public function suppliers_save(){
		$info = array('Company' => trim($_POST['Company']),
					  'Contacts'=> trim($_POST['Contacts']),
					  'Phone'   => trim($_POST['Phone']),
					  'Addr'    => trim($_POST['Addr']),
					  'EditTime'=> $_SERVER['REQUEST_TIME']
		);
		$db = M('supplier');
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID']=$TID;
			if($db->save($info)!==false){
				$this->success('供应商信息保存成功',U('suppliers_list'));die;
			}else{
				$this->error('供应商信息保存失败');die;
			}
		}else{
			$info['AddTime']=$info['EditTime'];
			if($db->add($info)>0){
				$this->success('供应商信息添加成功',U('suppliers_list'));die;
			}else{
				//echo $db->getLastSql();die;
				$this->error('供应商信息添加失败');die;
			}
		}
	}

	public function suppliers_del(){
		$TID = intval($_GET['TID']);
		$db = M('supplier');
		if($TID>0){
			if($db->delete($TID)!==false){
				$this->success('供应商删除成功');die;
			}else{
				$this->error('供应商删除失败');die;
			}
		}else{
			$this->success('供应商删除成功');die;
		}
	}
	
//eof 供应商管理

	//员工账单
	public function admin_bill(){
		$db_salary = M('admin_salary');
		
	}
    
	//管理员列表
	public function admin_list(){
		$res = array(
		            'db' => array('name'=>'admin',
		                         'field'=>'a.TID,a.UserName,a.RoleID,a.IsSys,b.Title RoleName,c.area_name',
		                        'join'=>'LEFT JOIN zp_admin_role b on a.RoleID=b.TID LEFT JOIN zp_area c on a.AID=c.TID',
		                        'order'=>'',
		                        'where'=>''),
		            'page'=>array(),
		            'display' => 'Admin/list'
		    );
		  $this->db_list($res);
	}
	
	//管理员信息编辑
	public function admin_edit(){
		$res = array(
		            'db' => array('name'=>'admin',
		                         'field'=>'a.TID,a.UserName,a.RoleID',
		                        'join'=>'',
		                        'order'=>'',
		                        'where'=>''),
		            'display' => 'Admin/edit'
		    );
		$db_role=M('admin_role');
		$role_list=$db_role->field('TID,Title')->where('TID<>8')->select();
		
		$this->assign('role_list',$role_list);
		$this->db_edit($res);
	}
	
	public function admin_save(){
		$db_admin=M('admin');
		$info=array(
			'UserName'=>trim($_POST['UserName']),
			'RoleID'=>intval($_POST['RoleID']),
			'EditTime'=>intval($_SERVER['REQUEST_TIME'])
		);
		$TID=intval($_POST['TID']);
		if($_POST['Pwd']!==$_POST['Repwd']){
			$this->error('两次输入密码不一致');die;
		}
		if($TID>0){
			$info['TID']=$TID;
			if(!empty($_POST['Pwd'])){
				$info['Password']=md5($_POST['Pwd']);
			}
			if($db_admin->save($info)!==false){
				$this->success('修改管理员信息成功',U('admin_list'));die;
			}else{
				$this->error('修改管理员信息失败');die;
			}
		}else{
			$info['RegTime']=$_SERVER['REQUEST_TIME'];
			if(empty($_POST['Pwd'])){
				$info['Password']=md5('123456');
			}else{
				$info['Password']=md5(trim($_POST['Pwd']));
			}
			if($db_admin->add($info)>0){
				$this->success('添加管理员成功',U('admin_list'));die;
			}else{
				$this->error('添加管理员失败');die;
			}
		}
	}

	//管理员信息删除
	public function admin_del(){
		$db_admin=M('admin');
		if($db_admin->where('IsSys=1 and TID='.$_GET['TID'])->count(0)>0){
			$this->error('该管理员是系统管理员，无法删除');die;
		}
		$res = array(
		            'db' => array('name'=>'admin'),
		            'file'=>array(''),
		            'edit'=>'',
		            'success'=>array('mes'=>'删除管理员成功','url'=>'admin_list'),
		            'error'=>array('mes'=>'删除管理员失败','url'=>'')
		        );
		        $this->db_del($res);
	}
	//员工结算工资
	public function admin_balance(){
				
		$TID = intval($_GET['TID']);
		$db_complain = M('user_complain');
		$db_order = M('user_order');
		$db_worker = M('admin');
		$db_system = M('system');
		if($TID>0){
			$work = $db_worker->field('TID,admin_name name,base_pay')->find($TID);
			$nowtime =  $_SERVER['REQUEST_TIME'];
			$order_list = $db_order->field('TID')->where('status>4 and worker_balance=1 and worker_id = '.$TID.' and AddTime<'.$nowtime)->select();
//			echo $db_order->getLastSql();die;
			$order_count = count($order_list);
			$order_about = '0';
			$complain_about = '0';
			foreach($order_list as $order){
				$order_about .= ','.$order['TID']; 
			}
			
			$com_list = $db_complain->field('TID')->where('status=2 and worker_balance=1 and worker_id='.$TID. ' and HeadingTime<'.$nowtime)->select();
			foreach($com_list as $com){
				$complain_about.=','.$com['TID'];
			}
			
			$work['order_about'] = $order_about;
			$work['complain_about'] = $complain_about;
			$fined = $db_complain->field('sum(fined_money)')->where('TID in ('.$complain_about.')')->select();
			$work['fined_money'] = $db_complain->where('TID in ('.$complain_about.')')->sum('fined_money');
			
			//获取提成
			$pacentage = $db_system->field('freight_percentage')->find(1);
			$work['order_percentage'] = $order_count*$pacentage['freight_percentage'];
			$work['total'] = $work['base_pay']+$work['order_percentage']-$work['fined_money'];
//			print_r($work);die;
			$this->assign('work',$work);
//			echo $db_complain->getLastSql();die;
			$this->display('Worker/balance');
		}else{
			$this->error('该员工不存在');die;
		}
	}
	
	
//工资单相关	
	public function admin_salary_add(){
//		print_r($_POST);die;
		$info = array(
			'admin_id' => intval($_POST['worker_id']),
			'AddTime'  => $_SERVER['REQUEST_TIME'],
			'base_pay' => floatval($_POST['base_pay']),
			'order_percentage'=> floatval($_POST['order_percentage']),
			'order_about' => trim($_POST['order_about']),
			'complain_about' => trim($_POST['complain_about']),
			'fined_money' => floatval($_POST['fined_money']),
			'Actually_pay'=>floatval($_POST['Actually_pay'])
			);
		if($info['admin_id']<=0){$this->error('员工信息为空');die;}
		
		$db_salary = M('admin_salary');
		$db_order = M('user_order');
		$db_complain = M('user_complain');
		if($db_order->where('TID in ('.$info['order_about'].')')->setField('worker_balance','2')===false){
			$this->error('订单结算失败');die;
		}
//		echo $db_order->getLastSql();die;
		if($db_complain->where('TID in ('.$info['complain_about'].')')->setField('worker_balance','2')===false){
			$this->error('投诉结算失败');die;
		}
//		echo $db_complain->getLastSql();die;
		
		if($db_salary->add($info)>0){
			$this->success('结算工资成功',U('Home/admin_salary_list'));die;
		}else{
			//echo $db_salary->getLastSql();die;
			$this->success('结算工资失败'.$db_salary->getLastSql());die;
		}
	}

//员工账单
	public function admin_salary_list(){
		$db_salary = M('admin_salary');
		
		//分页
		$total = $db_salary->count(0);
		$salary_list = $db_salary->alias('a')->field('a.Actually_pay,a.base_pay,a.order_percentage,a.fined_money,a.AddTime,b.admin_name,c.area_name')->join('LEFT JOIN zp_admin b on a.admin_id = b.TID')->join('LEFT JOIN zp_area c on b.area_id = c.TID')->select();
//		echo $db_salary->getLastSql();die;
		$this->assign('salary_list',$salary_list);
		$this->display('Worker/salary_list');
	}

//订单管理
	public function order_list(){
		$status = array(
			'0' => '未付款',
			'1' => '已取消',
			'2' => '已付款',
			'3' => '配送中',
			'4' => '已收货',
			'5' => '已评价');
		
		
		if(!empty($_GET['creat_table'])){
			$this->order_excel();
		}
		if(!empty($_GET['search'])){
			$ser['search']=1;
			if(!empty($_GET['show_id'])){
				$where['a.show_id']=array('like','%'.trim($_GET['show_id']).'%');
				$ser['show_id'] = trim($_GET['show_id']);
			}
			if(!empty($_GET['user_name'])){
				$where['b.user_name']=array('like','%'.trim($_GET['user_name']).'%');
				$ser['user_name'] = trim($_GET['user_name']);
			}
			$ser['status']=$_GET['status'];
			if($_GET['status']!='all'){
				$where['a.status']=intval($_GET['status']);
				$ser['status']=$where['a.status'];
			}
						
			list($start,$end)=explode('到',$_GET['time-range']);
			$ser['time-range']=trim($_GET['time-range']);
			if(!empty($start) && !empty($end)){
				$start=strtotime($start.' 00:00:00');
				$end=strtotime($end.' 23:59:59');
				$where['a.PayTime']=array('between',array($start,$end));
			}
			if(!empty($_GET['area_id'])){
				$where['a.area_id']=intval($_GET['area_id']);
				$ser['area_id']=intval($_GET['area_id']);
			}
			$UID =intval($_GET['UID']);
			if($UID>0){
				$where['a.user_id']=$UID;
				$ser['UID']=$UID;
			}
		}	
		$this->assign('search',$ser);
		$this->area_sel();
		
		$db_order = M('user_order');
		$db_goods_order = M('user_order_goods');
			
			
		$count = $db_order->alias('a')->where($where)->count(0);

		$this->page_set($count,15,'个订单',$ser);	
		$order_list = $db_order->alias('a')
							   ->field('a.TID,a.show_id,a.AddTime,a.AppointmentTime SendTime,a.status,a.Actually_pay,a.contact_name,a.contact_phone,a.address,a.Total_price,a.freightage_pay,a.mark,a.worker_id,a.worker_percentage,a.score_used,b.user_name,c.area_name')
							   ->join('LEFT JOIN zp_user b on a.user_id=b.TID LEFT JOIN zp_area c on a.area_id=c.TID')
							   ->where($where)->limit($this->page->firstRow,$this->page->listRows)->order('a.AddTime desc')->select();
							   
		//echo $db_order->getLastSql();die;
		
		foreach($order_list as $key=>$order){
			$order_list[$key]['status'] = $status[$order['status']];
			
			// $order_list[$key]['goods'] = $db_goods_order->field('goods_name,goods_small,price,type,number')->where('order_id='.$order['TID'])->select();
			// $order_list[$key]['goods_count']=count($order_list[$key]['goods'])+1;
			// foreach($order_list[$key]['goods'] as $k=>$goods){
			// 	if(empty($goods['goods_small'])){
			// 		$order_list[$key]['goods'][$k]['goods_small']='暂无图片';
			// 	}else{
			// 		$order_list[$key]['goods'][$k]['goods_small'] = '<img src="'.$goods['goods_small'].'" width="60px"/>';
			// 	}
			// }
			$order_list[$key]['short_address']=msubstr($order['address'],0,20);
			$order_list[$key]['short_mark']=msubstr($order['mark'],0,20);
			if($order['worker_id']>0){
				$NickName=M("worker")->where("TID=".$order['worker_id'])->getField("NickName");
				$order_list[$key]['NickName']=$NickName;
			}else{
				$order_list[$key]['NickName']='未指派';
			}
			if($order['SendTime']>0){
				$order_list[$key]['SendTime'] = date('Y-m-d G:i',$order['SendTime']).'到'.date('Y-m-d G:i',($order['SendTime']+3600)) ;
			}else if($order['SendTime']==0){
				$order_list[$key]['SendTime'] = '尽快配送';
			}else{
				$order_list[$key]['SendTime'] = '<span class="red">加急配送</span>';
			}
		}
		$this->assign('status',$status);
		$this->assign('page',$this->page->show());		
		$this->assign('order_list',$order_list);
		$this->display('Order/list');
	}
	
	public function order_edit(){
		$db_order = M('user_order');
		$db_order_goods = M('user_order_goods');
		$db_worker = M('worker');
		$TID = intval($_GET['TID']);
		if($TID>0){
			$re_order = $db_order->alias('a')->field('a.TID,a.AddTime,a.Actually_pay,a.contact_name,a.contact_phone,a.address,a.worker_id,a.area_id,a.status,a.coupon_pay,a.score_used,a.worker_percentage,a.Total_price,a.PayTime,a.show_id,b.user_name,c.area_name')->join('LEFT JOIN zp_user b on a.user_id=b.TID')->join('LEFT JOIN zp_area c on a.area_id=c.TID')->where('a.TID='.$TID)->find();
//			echo $db_order->getLastSql();die;
			$re_order['PayTime'] = empty($re_order['PayTime'])?'暂未付款':date('Y-m-d G:i',$re_order['PayTime']);
			$re_order['goods'] = $db_order_goods->field('goods_name,goods_small,number,price')->where('order_id='.$TID)->select();
			foreach($re_order['goods'] as $key=>$goods){
				if(empty($goods['goods_small'])){
					$re_order['goods'][$key]['goods_small'] = '暂无图片';
				}
				$re_order['goods'][$key]['goods_small'] = '<img src="'.$goods['goods_small'].'" width="60px"/>';
			}
//			$re_order['status'] = $status[$re_order['status']];
			$worker_list = $db_worker->field('TID,NickName workername')->where('AreaID='.$re_order['area_id'].' and status<>2')->select();
			
//			echo $db_admin->getLastSql();die;
			$this->assign('status',C('ORDER_STATUS'));
			$this->assign('worker_list',$worker_list);
			$this->assign('order',$re_order);
		}else{
			$this->error('请选择订单');
		}

		$this->display('Order/detail');
	}
	
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
	
	//保存订单修改
	public function order_save(){
		$info = array(
			'TID' => $_POST['TID'],
			'worker_id'=>intval($_POST['worker_id']),
			
		);
		if($info['worker_id']>0){
			$info['status'] = 3;
		}
		$info['worker_percentage']=doubleval($_POST['percentage']);
		$db_order = M('user_order');
		
		$mes = $db_order->field('user_id,area_id,status')->find($info['TID']);
		
		$status = $db_order->where('TID='.$info['TID'])->getField('status');
		if($status=='4'||$status=='5'){
			$info['status']=intval($status);
		}else if($status=='0'||$status=='1'){
			$this->error('该订单不在可编辑状态');die;
		}
		
		if($db_order->save($info)!==false){
			//send_mes($user_id,$area_id,$a_id,$SendTime)  0-立即发送 1- 注册完毕后发送 2- 获取优惠券时发送 3- 接单时发送
			
			if($info['worker_id']>0 && $mes['status']=='2'){
//				echo 'here';die;
				$this->send_mes($mes['user_id'],$mes['area_id'],$info['TID'],3);
			}
			$this->success('保存订单信息成功');
		}else{
			$this->error('保存订单信息失败');
		}
	}
	
	//检查是否用新订单
	public function check_new_order(){
		$db_order=M('user_order');
		$time = $_SERVER['REQUEST_TIME']-10;
		$count=$db_order->where('PayTime >='.$time)->count(0);
		echo $count;
	}
	//订单生成excel
	private function order_excel(){
		$status = array(
			'0' => '未付款',
			'1' => '已取消',
			'2' => '已付款',
			'3' => '配送中',
			'4' => '已收货',
			'5' => '已评价');
		if(!empty($_GET['show_id'])){
			$where['a.TID']=array('like','%'.trim($_GET['show_id']).'%');
			$ser['show_id'] = trim($_GET['show_id']);
		}
		if(!empty($_GET['user_name'])){
			$where['b.user_name']=array('like','%'.trim($_GET['user_name']).'%');
			$ser['user_name'] = trim($_GET['user_name']);
		}
		$ser['status']=$_GET['status'];
		if($_GET['status']!='all'){
			$where['a.status']=intval($_GET['status']);
		}
					
		list($start,$end)=explode('到',$_GET['time-range']);
		$ser['time-range']=trim($_GET['time-range']);
		if(!empty($start) && !empty($end)){
			$start=strtotime($start);
			$end=strtotime($end);
			$where['a.PayTime']=array('between',array($start,$end));
		}
		if(!empty($_GET['area_id'])){
			$where['a.area_id']=intval($_GET['area_id']);
			$ser['area_id']=intval($_GET['area_id']);
		}
		$UID =intval($_GET['UID']);
		if($UID>0){
			$where['a.user_id']=$UID;
			$ser['UID']=$UID;
		}
		$db_order=M('user_order');
		$db_goods_order=M('user_order_goods');
		$order_list = $db_order->alias('a')
							   ->field('a.TID,a.show_id,a.AddTime,a.AppointmentTime,a.SendTime,a.contact_name,a.coupon_pay,a.contact_phone,a.status,a.Actually_pay,a.Total_price,a.freightage_pay,a.score_used,b.NickName,c.area_name')
							   ->join('LEFT JOIN zp_worker b on a.worker_id=b.TID LEFT JOIN zp_area c on a.area_id=c.TID')
							   ->where($where)->limit($this->page->firstRow,$this->page->listRows)->order('a.AddTime desc')->select();
							   
//		echo $db_order->getLastSql();die;
		
		foreach($order_list as $key=>$order){
			$order_list[$key]['status'] = $status[$order['status']];
			$order_list[$key]['AddTime']= date('Y-m-d G:i',$order['AddTime']);
			$order_list[$key]['goods'] = $db_goods_order->field('goods_name,goods_small,price,type,number')->where('order_id='.$order['TID'])->select();
			$order_list[$key]['goods_count']=count($order_list[$key]['goods'])+1;
			//bof zhjhqk
			$order_list[$key]['goods_title_zhjhqk']='';
			$order_list[$key]['goods_number_zhjhqk']='';
			$order_list[$key]['goods_price_zhjhqk']='';
			//eof zhjhqk
			foreach($order_list[$key]['goods'] as $k=>$goods){
				if(empty($goods['goods_small'])){
					$order_list[$key]['goods'][$k]['goods_small']='暂无图片';
				}else{
					$order_list[$key]['goods'][$k]['goods_small'] = '<img src="'.$goods['goods_small'].'" width="60px"/>';
				}
				$order_list[$key]['goods_title_zhjhqk'].=$goods['goods_name']."\n\n";
				$order_list[$key]['goods_number_zhjhqk'].=$goods['number']."\n\n";
				$order_list[$key]['goods_price_zhjhqk'].=$goods['price']."\n\n";
			}
			if($order['SendTime']>0){
				$order_list[$key]['SendTime'] = date('Y-m-d G:i',$order['SendTime']);
			}else if($order['SendTime']=='0'){
				$order_list[$key]['SendTime'] = '尽快配送';
			}else{
				$order_list[$key]['SendTime'] = '加急配送';
			}
		}		
		vendor('PHPExcel.PHPExcel');
		// Create new PHPExcel object 创建excel类
		$objPHPExcel = new \PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		
		
		// Add some data
		//$objPHPExcel->setActiveSheetIndex(0);
		//bof zhjhqk
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','序号');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','订单号');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','下单时间');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','姓名');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','电话');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','商品名称');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','数量');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','商品价格');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','实付金额');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','订单状态');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','优惠券金额');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1','备注');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('M1','配送员');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N1','所在站点');
		
		//eof zhjhqk
		//excel表格从1开始计数,数组从0计数
		
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','销售信息');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','运费');
//		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','备注');
		//合并单元格
//		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
		
//		//填充数据
		foreach($order_list as $key=>$ex_item){
			$num=$key+2;
			$objPHPExcel->getActiveSheet()->getStyle('F'.$num)->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('G'.$num)->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$num)->getAlignment()->setWrapText(true);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $num-1);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $ex_item['TID']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $ex_item['AddTime']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $ex_item['contact_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$num, $ex_item['contact_phone']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$num, $ex_item['goods_title_zhjhqk']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$num, $ex_item['goods_number_zhjhqk']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $ex_item['goods_price_zhjhqk']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $ex_item['Actually_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $ex_item['status']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['coupon_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$num, $ex_item['mark']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$num, $ex_item['NickName']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$num, $ex_item['area_name']);
			
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $ex_item['product_info']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $ex_item['sell_info']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $ex_item['freight']);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['remarks']);
		}
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
		// Rename worksheet(重命名表格)
		$objPHPExcel->getActiveSheet()->setTitle('用户信息');
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="用户信息.'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;						
	}
	
//消息推送管理
	public function notice_list(){
		
		
		$gender = array(
			'0' => '所有人',
			'1' => '男客户',
			'2' => '女客户');
			
		$SendTime = array(
			'0'	=> '立即发送',
			'1' => '注册完毕后发送',
			'2'	=> '获得优惠券时发送',
			'3' => '接单时发送');		
			
		$db_mes = M('message');
		
		//分页显示
		$count = $db_mes->count(0);
		
		$this->page_set($count,15,'条信息',array());
		
		$mes_list = $db_mes->alias('a')->field('a.TID,a.title,a.content,a.AddTime,a.EditTime,a.SendTime,a.mes_area,a.mes_gender')->Order('EditTime')->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_mes->getLastSql();die;
		foreach($mes_list as $key=>$mes){
			if($mes['mes_area']==',0,'){
				$mes_list[$key]['mes_area'] = '所有站点';
			}else{
				
			}
			$mes_list[$key]['mes_gender'] = $gender[$mes['mes_gender']];
			$mes_list[$key]['SendTime'] = $SendTime[$mes['SendTime']];
		}
		$this->assign('page',$this->page->show());
		$this->assign('mes_list',$mes_list);
		$this->display('Message/list');
	}
	
	//编辑消息
	public function notice_edit(){			
			
		$gender = array(
			'0' => '所有人',
			'1' => '男客户',
			'2' => '女客户');		
		$SendTime = array(
			'0'	=> '立即发送',
			'1' => '注册完毕后发送',
			'2'	=> '获得优惠券时发送',
			'3' => '接单时发送');	
		if(isset($_GET['TID'])){
			$TID = intval($_GET['TID']);
			$db_mes = M('message');
			$mes = $db_mes->field('TID,title,content,SendTime,mes_area,mes_gender')->find($TID);
			$mes['mes_area'] = explode(',',$mes['mes_area']);

			$this->assign('mes',$mes);
		}
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
//		echo $db_area->getLastSql();die;
		//站点列表
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
		$this->assign('area',$area_list);		
		//用户列表
		$this->assign('gender',$gender);
		
		//发送时列表
		$this->assign('SendTime',$SendTime);
		
		$this->display('Message/edit');
	}
	public function test_getui()
	{
		vendor("Getui.Getui");
		$Getui=new \Getui();
		$Getui->setddd('测试信息zhjhqk');
		$Getui->test();die;
		// $Getui->notice_title='你好，世界！';
		// $Getui->notice_content='一花一世界，一人一个家';
		// $Getui->CID='9ee325e5846af12a4992eb377ed0e471';
		$notice_title='你好，世界！';
		$notice_content='一花一世界，一人一个家';
		$CID='9ee325e5846af12a4992eb377ed0e471';
		$Getui::pushMessageToSingle($CID,$notice_title,$notice_content);
	}
	//保存通知信息
	public function notice_save(){
		$db_user = M('user');
		$db_mes = M('message');
		$db_u_mes = M('user_message');
//		print_r($_POST);die;
		$info = array(
			'title'     => trim(strval($_POST['title'])),
			'content'   => trim(strval($_POST['content'])),
			'mes_gender'=> intval($_POST['gender']),
			'SendTime'  => intval($_POST['SendTime']),
			'EditTime'  => $_SERVER['REQUEST_TIME']);
		if(in_array(0,$_POST['mes_area']) || empty($_POST['mes_area'])){
			$info['mes_area'] = ',0,';
		}else{
			$info['mes_area'] = ','.implode(',',$_POST['mes_area']).',';
		}		
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			$re_mes = $db_mes->save($info);	
			if($re_mes!==false){
				$this->success('保存消息成功',U('Home/notice_list'));die;
			}else{
				$this->error('保存消息失败');die;
			}			
		}else{
			$info['AddTime'] = $info['EditTime'];
			$re_mes = $db_mes->add($info);
			if($info['SendTime']==0){
				if($info['mes_gender']!=0){
					$user['gender'] = intval($info['mes_gender']);
				}
				if($info['mes_area']!=0){
					$user['user_area'] = array('in',$info['mes_area']);
				}
//				print_r($user);die;
				$send_user = $db_user->field('TID,user_name,getui_cid')->where($user)->select();
//				echo $db_user->getLastSql();die;
				$u_mes = array(
					'mes_id' => $re_mes,  //消息id
					'user_id'=> 0,        
					'status' => 2,        //未读
					'a_id'   => 0,        //先关id
					'AddTime'=> $info['AddTime'] //发送时间
					);
				vendor("Getui.Getui");
				$Getui=new \Getui();
				// $Getui->__set("notice_title",$info['Title']);
				// $Getui->__set("notice_content",$info['content']);
				foreach($send_user as $user){					
					$u_mes['user_id'] = $user['TID'];  //用户id
					if($db_u_mes->add($u_mes)>0){
						if(!empty($user['getui_cid'])){
							// $Getui->__set("CID",$user['getui_cid']);
							//$Getui::pushMessageToSingle();
							str_replace("{user}", $user['user_name'], $info['Title']);
							str_replace("{user}", $user['user_name'], $info['content']);
							$Getui::pushMessageToSingle($user['getui_cid'],$info['Title'],$info['content']);
						}
						continue;
					}else{
						$this->error('通知发送失败');
					}
				}
			}
//			echo $db_mes->getLastSql();die;
			//根据条件判断是否发送消息
			if($re_mes>0){
				$this->success('保存消息成功',U('Home/notice_list'));die;
			}else{
				//echo $db_mes->getLastSql();die;
				$this->error('保存消息失败');die;
			}			
		}
	}
	
	//删除通知
	public function notice_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_mes = M('message');
			if($db_mes->delete($TID)!==false){
				$this->success('通知删除成功',U('Home/notice_list'));die;
			}else{
				$this->error('通知删除失败');die;
			}
		}else{
			$this->error('该通知不存在');
		}
	}

	
//卡券管理
	public function coupon_list(){	
				
			
//		$SendTime = array(
//			'0'	=> '立即发送',
//			'1' => '注册完毕后发送',
//			'2'	=> '支付成功后发送',
//			'3' => '分享成功后发送',
//			'4' => '活动时领取');		
		$SendTime=C('SendTime');		
		$db_coupon = M('coupon');
		$db_area = M('area');
		$db_cate = M('category');
		
		$count = $db_coupon->where($info)->count(0);
		$this->page_set($count,15,'张优惠券',array());
		
		$cp_list = $db_coupon->field('TID,title,pay,AddTime,EditTime,TimeLimit,SendTime,coupon_area,pay_limit,phone_limit,cate_limit')->order('EditTime')->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_coupon->getLastSql();die;
		foreach($cp_list as $key=>$coupon){
			if($coupon['coupon_area']==0){
				$cp_list[$key]['coupon_area'] = '所有站点';
			}else{
				$area_list = $db_area->field('area_name')->where('TID in ('.$coupon['coupon_area'].')')->select();
				$area_limit='';
				foreach($area_list as $area){
					if(empty($area_limit)){
						$area_limit .= $area['area_name'];
					}else{
						$area_limit .='、'. $area['area_name'];
					}
				}
				$cp_list[$key]['coupon_area']=$area_limit;
			}
			$cp_list[$key]['coupon_gender'] = $gender[$coupon['coupon_gender']];
			$cp_list[$key]['SendTime'] = $SendTime[$coupon['SendTime']];
			$cate_list = $db_cate->field('cate_name')->where('TID in ('.$coupon['cate_limit'].')')->select();
//				echo $db_cate->getLastSql();die;
				$cate_limit='';
				foreach($cate_list as $cate){
					if(empty($cate_limit)){
						$cate_limit .= $cate['cate_name'];
					}else{
						$cate_limit .='、'. $cate['cate_name'];
					}
				}
//				echo $cate_limit;die;
				$cp_list[$key]['cate_limit']=($cate_limit==''?'所有商品':$cate_limit);
			
		}
		$this->assign('page',$this->page->show());	
		$this->assign('cp_list',$cp_list);		
		$this->display('Coupon/list');
	}
	
	public function coupon_edit(){
//		$this->track['coupon'][0] = 'class= "active open"';
//		$this->track['coupon']['edit']='class = "active"';
//		$this->assign('track',$this->track);			
		
		$gender = array(
			'0' => '所有人',
			'1' => '男客户',
			'2' => '女客户');
			
		$SendTime = array(
			'0'	=> '立即发送',
			'1' => '注册完毕后发送',
			'2'	=> '支付成功后发送',
			'3' => '分享成功后发送',
			'4' => '活动时领取',
			'5' => '手动发送');		
		
		if(isset($_GET['TID'])){
			$TID = intval($_GET['TID']);
			$db_coupon = M('coupon');
			$coupon = $db_coupon->field('TID,title,pay,TimeLimit,pay_limit,cate_limit,phone_limit,SendTime,coupon_area,coupon_gender')->find($TID);
			$coupon['coupon_area'] = explode(',',$coupon['coupon_area']);
//			$coupon['cate_limit'] = explode(',',$coupon['cate_limit']);
			$this->assign('coupon',$coupon);
		}
		
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
//		echo $db_area->getLastSql();die;
		//站点列表
		$db_area = M('area');
		$area_list = $db_area->field('TID,area_name')->select();
		$this->assign('area',$area_list);		
		//用户列表
		$this->assign('gender',$gender);
		
		//属性获取
//		$cate = $this->cate_ergodic();		

		$cate = $this->cate_ergodic_v();		
//		$this->assign('cate',$cate);
		$this->assign('cate',$cate);	
			
		
		//发送时列表
		$this->assign('SendTime',C('SendTime'));
		
		$this->display('Coupon/edit');	
	}
	
	//优惠券保存
	public function coupon_save(){
		$db_coupon = M('coupon');
//		print_r($_POST);die;
		$info = array(
			'title' => trim($_POST['title']),
			'pay'     => floatval($_POST['pay']),          //优惠券金额
			'pay_limit' => floatval($_POST['pay_limit']),
			'TimeLimit'   => intval($_POST['limit']),      //使用期限
			'coupon_gender'=> 0,    //性别限制
			'SendTime'  => intval($_POST['SendTime']),     //发放条件
			'EditTime'  => $_SERVER['REQUEST_TIME']        //编辑时间
		);      
		if(empty($info['title'])){
			$this->error('标题为空');die;
		}	
		if(empty($info['pay'])){
			$this->error('请输入正确金额');die;
		}
		if($info['TimeLimit']<=0){
			$this->error('使用时间期限必须大于0');die;
		}
		if(in_array(0,$_POST['coupon_area']) || empty($_POST['coupon_area'])){
			$info['coupon_area'] = 0;
		}else{
			$info['coupon_area'] = '0,'.implode(',',$_POST['coupon_area']).',0';
		}
		
		if(in_array(0,$_POST['cate_limit']) || empty($_POST['cate_limit'])){
			$info['cate_limit'] = 0;
		}else{
			$info['cate_limit'] = '0,'.implode(',',$_POST['cate_limit']).',0';
		}
			
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			$re_coupon = $db_coupon->save($info);	
			if($re_coupon!==false){
				$this->success('保存优惠券信息成功',U('Home/Coupon_list'));die;
			}else{
				$this->error('保存优惠券信息失败');die;
			}			
		}else{
			$info['AddTime'] = $info['EditTime'];
			$re_coupon = $db_coupon->add($info);
//			echo $db_mes->getLastSql();die;
			//根据条件判断是否发送消息
			if($re_coupon>0){
			
				if($info['SendTime']==0){
					if($this->send_coupon($re_coupon,$db_coupon)==false){
						$this->error('立即发送失败');die;
					}
				}
				$this->success('添加优惠券信息成功',U('Home/Coupon_list'));die;
			}else{
				$this->error('添加优惠券信息失败');die;
			}			
		}		
	}
	
	//优惠券删除
	public function coupon_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_coupon = M('coupon');
			$db_user_coupon=M('user_coupon');
			
			if($db_user_coupon->where('coupon_id='.$TID)->count(0)>0){
				$this->error('该优惠券已经有人领取，无法删除');die;
			}
			
			if($db_coupon->delete($TID)!==false){
				$this->success('优惠券删除成功',U('Home/Coupon_list'));die;
			}else{
				$this->error('优惠券删除失败');die;
			}
			
		}else{
			$this->error('该优惠券不存在');
		}		
	}

	//优惠券领取列表
	public function coupon_user_list(){
		$where['a.coupon_id']=intval($_GET['coupon_id']);
		
		$res = array(
		            'db' => array('name'=>'user_coupon',
		                         'field'=>'a.status,a.AddTime,a.DeadTime,b.user_name,c.title,c.pay',
		                        'join'=>'LEFT JOIN zp_user b on a.user_id=b.TID LEFT JOIN zp_coupon c on a.coupon_id=c.TID',
		                        'order'=>'a.TID desc',
		                        'where'=>$where),
		            'page'=>array(),
		            'display' => 'Coupon/user_list'
		);
		
		$this->db_list($res);
	}

//投诉管理
	//投诉列表
	public function complain_list(){
		
		$db_com = M('user_complain');
		$reson =array(
			'1' => '服务态度恶劣',
			'2' => '送错了',
			'3' => '东西有破损',
			'4' => '配送员迟到了',
			'5' => '骚扰客户');
		
		$status = array(
			'1' => '未处理',
			'2' => '已处理');
		$balance = array(
			'1' => '未结算',
			'2' => '已结算');
		$count = $db_com->alias('a')->count(0);
		$this->page_set($count,15,'条投诉信息',$parameter);	
			
		$com_list = $db_com->alias('a')
						->field('a.TID,a.user_id,a.reason,a.order_id,a.result,a.Admin_id,a.AddTime,a.HeadingTime,a.fined_money,a.status,a.worker_balance,b.user_name,c.NikeName admin_name,d.NickName worker')
						->join('LEFT JOIN zp_user b on a.user_id=b.TID')
						->join('LEFT JOIN zp_admin c on a.Admin_id = c.TID')
						->join('LEFT JOIN zp_worker d on a.worker_id= d.TID')
						->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_com->getLastSql();die;
		foreach($com_list as $key=>$com){
			$com_list[$key]['reason'] = $reson[$com['reason']];
			$com_list[$key]['status'] = $status[$com['status']];
			$com_list[$key]['worker_balance'] = $balance[$com['worker_balance']];
			if($com['status']=='1'){
				$com_list[$key]['HeadingTime']='暂无';
			}else{
				$com_list[$key]['HeadingTime'] = date("Y-m-d G:i",intval($com['HeadingTime']));
			}
		}
		$this->assign('page',$this->page->show());	
		$this->assign('com_list',$com_list);
		$this->display('Complain/list');
		
	}
	
	//投诉处理
	public function complain_edit(){
		$TID = intval($_GET['TID']);
		$reson =array(
			'1' => '服务态度恶劣',
			'2' => '送错了',
			'3' => '东西有破损',
			'4' => '配送员迟到了',
			'5' => '骚扰客户');
					
		$db_complain = M('user_complain');
		if($TID>0){
			 $complain = $db_complain->alias('a')->field('a.TID,a.reason,a.result,a.Admin_id,b.user_name,d.admin_name worker,e.area_name')->join('LEFT JOIN zp_user b on a.user_id=b.TID')->join('LEFT JOIN zp_admin c on a.Admin_id = c.TID')->join('LEFT JOIN zp_admin d on a.worker_id= d.TID')->join('LEFT JOIN zp_area e on d.area_id=e.TID')->where('a.TID='.$TID)->find();
//			 echo $db_complain->getLastSql();
			 //print_r($complain);
			 $complain['reason'] = $reson[$complain['reason']];
			 $this->assign('complain',$complain);
		}else{
			$this->error('该投诉不存在');die;
		}
		$this->display('Complain/edit');
	}
	
	//处理结果保存
	public function complain_save(){
		$AID = intval($this->roll_area['TID']);
		$info =array(
			'TID'         => intval($_POST['TID']),
			'HeadingTime' => $_SERVER['REQUEST_TIME'],
			'result'      => trim(strval($_POST['result'])),
			'fined_money' => floatval($_POST['fined_money']),
			'Admin_id'    => $AID,
			'status'      => 2);
		$db_com = M('user_complain');
		if($db_com->save($info)!== false){
			$this->success('处理成功',U('Home/complain_list'));die;
		}else{
			$this->error('处理失败');die;
		}	
	}
	
//用户反馈
	public function recomment_list(){
		$this->track['recomment'][0] = 'class= "active open"';
		$this->track['recomment']['list']='class = "active"';
		$this->assign('track',$this->track);	
				
		$db_recom = M('user_recomment');
		$count = $db_recom->count(0);
		$this->page_set($count,15,'条投诉信息',$parameter);			
		$recom_list = $db_recom->alias('a')->field('a.TID,b.user_name,a.Contents,a.AddTime')->join('LEFT JOIN zp_user b on a.user_id=b.TID')->order('a.AddTime')->limit($this->page->firstRow,$this->page->listRows)->select();
		$this->assign('page',$this->page->show());
		$this->assign('recom',$recom_list);
		$this->display('Recomment/list');
	}
	
	//删除用户反馈
	public function recomment_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_recom = M('user_recomment');
			if($db_recom->delete($TID)!=false){
				$this->success('删除用户反馈成功');die;
			}else{
				$this->error('删除用户反馈失败');die;
			}	
		}else{
			$this->error('该用户反馈不存在');
		}
	}
	
//APP使用时参数管理
	//APP使用参数编辑
	public function app_parament_edit(){		
		
		$db_sys = M('system');
		$sys_list = $db_sys->find(1);
		$this->assign('system',$sys_list);
		$this->display('System/edit');
	}
	
	//APP使用参数保存
	public function app_parament_save(){
		$info = array(
			'score_limit' => intval($_POST['score_limit']),
			'freight'     => floatval($_POST['freight']),
			'urgent_freight'=>floatval($_POST['urgent_freight']),
			'freight_time'=> intval($_POST['freight_time']),
			'score_rate'  => intval($_POST['score_rate']),
			'freight_percentage' =>doubleval($_POST['freight_pecentage']),
			'score_get'   => intval($_POST['score_get']),
			'phone'		 => trim($_POST['phone']),
			'EditTime'    => $_SERVER['REQUEST_TIME']
		);
		$TID = intval($_POST['TID']);
		$db_sys = M('system');
		if($TID>0){
			$info['TID'] = $TID;
			if($db_sys->save($info)!==false){
				$this->success('保存APP使用参数成功');
			}else{
				$this->error('保存APP使用参数失败');
			}
		}else{
			if($db_sys->add($info)>0){
				$this->success('添加APP使用参数成功');
			}else{
				$this->error('添加APP使用参数失败');
			}
		}
	}
/*	
	
//活动管理
	//活动列表
	public function activity_list(){
		$this->track['activity'][0] = 'class= "active open"';
		$this->track['activity']['list']='class = "active"';
		$this->assign('track',$this->track);				
		
		$db_activity = M('activity');
		$act_list = $db_activity->alias('a')->field('a.TID,a.area_id,a.start_time,a.end_time,a.AddTime,a.EditTime,a.show,a.OrderID,b.area_name')->join('LEFT JOIN zp_area b on a.area_id=b.TID')->order('a.area_id,a.OrderID')->limit($this->page->firstRow,$this->page->listRows)->select();
//		echo $db_activity->getLastSql();die;
		foreach($act_list as $key=>$act){
			if(!empty($act['show'])){
				$act_list[$key]['show'] = '<img width="50px" src="'.$act['show'].'"/>';
			}
		}
		$this->assign('act_list',$act_list);
		$this->display('Activity/list');
	}
	
	//活动编辑
	public function activity_edit(){
		$this->track['activity'][0] = 'class= "active open"';
		$this->track['activity']['edit']='class = "active"';
		$this->assign('track',$this->track);		
		
		$db_area = M('area');
		$TID = 	intval($_GET['TID']);
		$re_act['start_time'] = $_SERVER['REQUEST_TIME'];
		$re_act['end_time'] = $re_act['start_time'];
		if($TID>0){
			$db_act = M('activity');
			$re_act = $db_act->field('TID,area_id,start_time,end_time,show,content')->find($TID);
			if(!empty($re_act['show'])){
				$re_act['show'] = '<img width="50px" src="'.$re_act['show'].'"/>';	
			}
		}
		$this->assign('activity',$re_act);
		$area_list = $db_area->field('TID,area_name')->select();
//		echo $db_area->getLastSql();die;
		$this->assign('area',$area_list);
		
		$this->display('Activity/edit');
	}
	
	//保存活动信息
	public function activity_save(){
		$db_act = M('activity');
		$info =array(
			'area_id'  	=> intval($_POST['area_id']),
			'content'  	=> trim(strval($_POST['content'])),
			'start_time'=> strtotime($_POST['start_time']),
			'end_time'  => strtotime($_POST['end_time']),
			'EditTime' => $_SERVER['REQUEST_TIME'],
			);
		if($info['area_id']==0){
			$this->error('请选择有效站点');die;
		}
		$file = $this->upload('Activity/');
		if(!empty($file)){
			$info['show'] = $this->rootpath.$file[0]['savepath'].$file[0]['savename'];
		}
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			if(isset($info['show'])){
				$del = $db_act->field('show')->find($TID);
				$this->file_del($del['show']);
			}
			$re_act = $db_act->save($info);
		}else{
			$info['AddTime'] = $info['EditTime'];
			$re_act = $db_act->add($info);
		}
//		echo $db_act->getLastSql();die;
		if($re_act!==false){
			$this->success('保存活动信息成功',U('activity_list'));die;
		}else{
			$this->error('保存活动信息失败');die;
		}
	}
	
	//活动删除
	public function activity_del(){
		$TID = intval($_GET['TID']);
		if($TID>0){
			$db_act = M('activity');
			$del = $db_act->field('show')->find($TID);
			$this->file_del($del['show']);
			
			$re_act = $db_act->delete($TID);
			if($re_act!==false){
				$this->success('删除活动信息成功',U('activity_list'));die;
			}else{
				$this->error('删除活动信息失败');
			}
		}else{
			$this->error('该活动信息不存在');
		}	
	}
*/

//接口管理
	//接口列表
	public function api_list(){
				
		$db_api = M('api');
		//分页显示
		if(!empty($_GET['search'])){
			$info['api_name']=array('like','%'.trim($_GET['search']).'%');
			$parameter['search'] =  trim($_GET['search']);
		}
		
		if(!empty($_POST['search'])){
			$info['api_name']=array('like','%'.trim($_POST['search']).'%');
			$parameter['search'] =  trim($_POST['search']);
		}
		$count = $db_api->where($info)->count(0);
//		$Page = new \Think\Page($count,15);

		$this->page_set($count,15,'个接口',$parameter);
		
		$re_api = $db_api->field()->limit($this->page->firstRow,$this->page->listRows)->where($info)->select();
		$this->assign('page',$this->page->show());
		$this->assign('api',$re_api);
		$this->display('API/list');
	}
	
	//接口编辑
	public function api_edit(){
		$this->track['api'][0] = 'class= "active open"';
		$this->track['api']['edit']='class = "active"';
		$this->assign('track',$this->track);		
		
		if(isset($_GET['TID'])){
			$db_api = M('api');
			$TID = intval($_GET['TID']);
			$re_api = $db_api->field()->find($TID);
			$this->assign('api',$re_api); 
		}
		$this->display('API/edit');
	}
	
	public function api_save(){
		$db_api = M('api');
		$info = array(
			'api_name' => strval($_POST['name']),
			'info_get' => strval($_POST['info_get']),
			'url'      => strval($_POST['url']),
			'info_post'=> strval($_POST['info_post']),
			'status'   => 0);
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			$re_api = $db_api->save($info);
		}else{
			$re_api = $db_api->add($info);
		}		
		if($re_api!==false){
			$this->success('接口信息保存成功');
		}else{
			$this->error('接口信息保存失败');
		}
	}
	
	public function api_del(){
		$TID = intval($_GET['TID']);
		if(M('api')->delete($TID)!==false){
			$this->success('接口信息删除成功');die;
		}else{
			$this->error('接口信息删除失败');die;
		}		
	}
	
	//调试接口
	public function api_test(){
		$info = array(
			'TID' => intval($_GET['TID']),
			'status'=> 1
		);
		$db_api = M('api');
		if($db_api->save($info)!==false){
			$this->success('接口状态更新成功');die;
		}else{
			$this->error();
		}
	}
	
//bof热门搜索词管理
	//列表
	public function hot_search_list(){
//		dump($_POST['OrderID']);
//		$this->track['hot_search'][0] = 'class= "active open"';
//		$this->track['hot_search']['list']='class = "active"';
//		$this->assign('track',$this->track);
		$db_hot = M('hot_search');
		if(!empty($_POST['OrderID'])){
			foreach($_POST['OrderID'] as $key=>$oid){
				$order = array(
				'TID'     => intval($_POST['TID'][$key]),
				'OrderID' => intval($oid));
				$db_hot->save($order);
			}
		}
		
		if(!empty($_GET['search'])){
			if(!empty($_GET['keyword'])){
				$where['a.keyword']=array('like','%'.trim($_GET['keyword'].'%'));
				$info['keyword']=trim($_GET['keyword']);
			}
		}
		$this->assign('info',$info);
		$hot_list = $db_hot->alias('a')
						->field('a.TID,a.keyword,a.OrderID,a.AddTime,a.EditTime,a.times')
						->where($where)
						->order('a.OrderID asc')
						->select();
		
//		echo $db_hot->getLastSql();die;
		$this->assign('hot_list',$hot_list);
		$this->display('Hot_search/list');
	}
	
	//编辑
	public function hot_search_edit(){
		$this->track['hot_search'][0] = 'class= "active open"';
		$this->track['hot_search']['edit']='class = "active"';		
		$this->assign('track',$this->track);		
		//所在站点
		$db_area = M('area');		
		$area_list = $db_area->field('TID,area_name')->select();	
		$this->assign('area',$area_list);	

		$TID = intval($_GET['TID']);
		$db_hot = M('hot_search');
		if($TID>0){
			$re_hot = $db_hot->find($TID);
			$this->assign('hot',$re_hot);
		}
		$this->display('Hot_search/edit');
	}
	
	//保存
	public function hot_search_save(){
		$db_hot = M('hot_search');
		$info = array(
			'keyword' => strval($_POST['keyword']),
			'area_id' => intval($_POST['area_id']),
			'EditTime'=> $_SERVER['REQUEST_TIME'],
			'OrderID' => intval($_POST['OrderID'])
		);
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;

			if($db_hot->save($info)!==false){
				$this->success('修改搜索词成功',U('Home/hot_search_list'));die;
			}else{
				$this->error('修改搜索词失败');die;	
			}
		}else{
			$info['AddTime'] = $info['EditTime'];
			if($db_hot->add($info)>0){
				$this->success('添加搜索词成功',U('Home/hot_search_list'));die;
			}else{
				$this->error('添加搜索词失败');die;
			}
		}	
	}
	
	//删除
	public function hot_search_del(){
		$db_hot = M('hot_serach');		
		$TID = intval($_GET['TID']);
		if($TID>0){
			if($db_hot->delete($TID)!==false){
				$this->success('删除搜索词成功');die;
			}else{
				$this->error('删除搜索词失败');die;
			}
		}else{
			$this->success('删除搜索词成功');
		}
	}
	
//eof热门搜索词管理		

//bof 广告管理
	public function ad_list(){
		$db_ad = M('ad');
		$count = $db_ad->alias('a')->where($info)->count(0);
		$this->page_set($count,15,'篇文章',array());	
		
		$list=$db_ad->alias('a')
					->field('a.TID,a.Title,a.Image,a.Position,a.ReadTimes,a.OrderID,a.Link,a.EditTime,a.AddTime,b.area_name,c.title IndexTitle')
					->join('zp_index_data c on a.PositionID=c.TID')
					->join('zp_area b on a.AreaID=b.TID')
					->limit($this->page->firstRow,$this->page->listRows)
					->where($info)->select();
		
//		echo $db_ad->getLastSql();die; 
		$this->assign('Link',C('AD_LINK'));
		$this->assign('Pos',C('AD_POSITION'));
		$this->assign('list',$list);
		$this->assign('page',$this->page->show());		
		$this->display('Ad/list');
	}
	public function ad_edit(){
	
//		$link = C('AD_LINK');
		$db = M('ad');
		$db_goods = M('goods');
		$db_text = M('text');
		$db_coupon=M('coupon');	
		$db_goods=M('area_goods');	
		//bof zhjhqk add
		$db_ac = M('ac_goods');
		//eof zhjhqk add
		$TID = intval($_GET['TID']);
		if($TID>0){
			$info = $db->field('TID,Title,Image,Position,PositionID,OrderID,Link,Link_ID,AreaID')->find($TID);
			$info['Pos']=$info['PositionID'].'-'.$info['Position'];
			$db_index=  M('index_data');
			$ad_pos = C('AD_POSITION');
			$pos = array();
			$index_list = $db_index->field('TID,title,cate')->where('area_id='.$info['AreaID'])->select();
			foreach($index_list as $k=>$index){
				if($index['cate']=='1'){
					foreach($ad_pos as $key=>$v){
						$pos[]=array('value'=>$index['TID'].'-'.$key,'title'=>$index['title'].$v);
					}
				}else{
					$pos[]=array('value'=>$index['TID'].'-1','title'=>$index['title'].'轮播广告');
				}
			}
			switch($info['Link']){
				case 1:
					$list = $db_text->field('TID,title')->where('area_id='.$info['AreaID'])->select();
					break;
				case 2:
				case 3:
					$list = $db_goods->alias('a')->field("a.TID,concat(b.title,'[',c.type,']' ) title")
								->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
								->where('a.is_onsale=1 and b.type<>4 and a.area_id='.$info['AreaID'])
								->select();
					break;
				case 4:
				//bof zhjhqk add
				$list = $db_ac->alias('a')
								->field('a.TID,c.title')
								->join('LEFT JOIN zp_area_goods b on a.area_store_goods_id=b.TID LEFT JOIN zp_goods_data c on b.goods_data_id=c.TID')
								->where('a.status=1 and a.area_id = '.$info['AreaID'])->select();
				//eof zhjhqk add				
					break;
				case 5:
				//bof zhjhqk
					$list = $db_goods->alias('a')
								->field("a.TID, concat(b.title,'[',c.type,']' ) title")
								->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
								->where('a.is_onsale=1 and b.type=2 and a.area_id='.$info['AreaID'])->select();
				//eof zhjhqk
					break;
				case 6:
					$list = $db_coupon->field('TID,title')->where('sendtime=4 and coupon_area in (0,'.$info['AreaID'].')')->select();
	//				echo $db_coupon->getLastSql();die;
					break;
			}
//			dump($info);die;
//			echo $db_ac->getLastSql();die;
//			dump($list);die;
			$this->assign('list',$list);
			$this->assign('pos',$pos);
			$this->assign('info',$info);			
		}
		
		$this->area_sel();
		$this->assign('link',C('AD_LINK'));		
		$this->display('Ad/edit');
	}
	
	public function ajax_get_position(){
		$area_id = I('area');
		$db_index=  M('index_data');
		$ad_pos = C('AD_POSITION');
		$pos = array();
		$index_list = $db_index->field('TID,title,cate')->where('area_id='.$area_id)->select();
		foreach($index_list as $k=>$index){
			if($index['cate']=='1'){
				foreach($ad_pos as $key=>$v){
					$pos[]=array('value'=>$index['TID'].'-'.$key,'title'=>$index['title'].$v);
				}
			}else{
				$pos[]=array('value'=>$index['TID'].'-1','title'=>$index['title'].'轮播广告(705px*480px)');
			}
		}
		echo json_encode($pos);
	}
	
	public function ajax_ad_link(){
		$area_id= I('area');
		$link = I('link');
		
		$db_goods = M('area_goods');
		$db_text = M('text');
		$db_coupon=M('coupon');
		//bof zhjhqk add
		$db_ac = M('ac_goods');
		//eof zhjhqk add
		switch($link){
			case 1:
				$list = $db_text->field('TID,title')->where('area_id='.$area_id)->select();
				break;
			case 2:
			case 3:
				$list = $db_goods->alias('a')
							->field("a.TID, concat(b.title,'[',c.type,']' ) title")
							->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
							->where('a.is_onsale=1 and b.type<>4 and a.area_id='.$area_id)->select();
				break;
			case 4:
			//bof zhjhqk add
			$list = $db_ac->alias('a')
							->field('a.TID,c.title')
							->join('LEFT JOIN zp_area_goods b on a.area_store_goods_id=b.TID LEFT JOIN zp_goods_data c on b.goods_data_id=c.TID')
							->where('status=1 and a.area_id = '.$area_id)->select();
			//eof zhjhqk add				
				break;
			case 5:
			//bof zhjhqk
				$list = $db_goods->alias('a')
							->field("a.TID, concat(b.title,'[',c.type,']' ) title")
							->join('LEFT JOIN zp_goods_data b on a.goods_data_id=b.TID LEFT JOIN zp_seller_type c on b.type=c.TID')
							->where('a.is_onsale=1 and b.type=2 and a.area_id='.$area_id)->select();
			//eof zhjhqk
				break;
			case 6:
				$list = $db_coupon->field('TID,title')->where('sendtime=4 and coupon_area in (0,'.$area_id.')')->select();
//				echo $db_coupon->getLastSql();die;
				break;
		}
		if(empty($list)){
			$list=array();
		}
		echo json_encode($list);
	}
	
	public function ad_save(){
//		print_r($_POST);die;
		$db = M('ad');
		
		$pos = explode('-',$_POST['position']);
		$info = array(
				'AreaID'=>intval($_POST['area_id']),
				'Position'=>intval($pos[1]),
				'PositionID'=>intval($pos[0]),
				'Link'=>intval($_POST['link']),
				'OrderID'=>intval($_POST['OrderID']),
				'Title'=>trim($_POST['title']),
				'EditTime'=>$_SERVER['REQUEST_TIME']
				);
				//保存图片
//		'1'=>'文章',
//		'2'=>'商品列表',
//		'3'=>'商品详情',
//		'4'=>'抢购商品详情',
//		'5'=>'自由单点页面',
//		'6'=>'领取活动优惠券'		
		switch($info['Link']){
			case 1:
			case 3:
			case 6:
				$info['Link_ID']='0,'.intval($_POST['about']).',0';
				break;
			//bof zhjhqk
			case 4:
				$info['Link_ID']='0,'.intval($_POST['about']).',0';
				break;
			//eof zhjhqk
			case 2:
				$info['Link_ID']='0,'.implode(',',$_POST['about']).',0';
				break;
			default:
				$info['Link_ID']='0';
				break;
		}
		$upload = $this->upload('Ad/');
		if(!empty($upload)){
			$info['Image'] = $this->rootpath.$upload['0']['savepath'].$upload['0']['savename'];
		}
		if(($info['Position']!=1 && $info['Position']!=8) && $db->where('Position='.$info['Position'].' and PositionID='.$info['PositionID'])->count(0)>0){
			$TID = intval($db->where('Position='.$info['Position'].' and PositionID='.$info['PositionID'])->getField('TID'));
		}else{
			$TID = intval($_POST['TID']);
		}
		
		if($TID>0){
			$info['TID']=$TID;
			if($db->save($info)!==false){
				$this->success('修改广告成功',U('ad_list'));die;
			}else{
				$this->error('修改广告失败');die;
			}
		}else{
			$info['AddTime']=$info['EditTime'];
			if($db->add($info)>0){
				$this->success('添加广告成功',U('ad_list'));die;
			}else{
				$this->error('添加广告失败');die;
			}
		}
	}	

	public function ad_del(){
		$TID = intval($_GET['TID']);
		$db_ad = M('ad');
		if($db_ad->delete($TID)!==false){
			$this->success('');
		}
	}
//eof 广告管理

//bof 文章管理
	public function text_list(){
		$db_text=M('text');
		
		//分页
		$count = $db_text->alias('a')->where($info)->count(0);
		$this->page_set($count,15,'篇文章',array());		
		$list=$db_text->alias('a')
					->field('a.TID,a.title,a.read_times,a.EditTime,a.AddTime,b.area_name')
					->join('zp_area b on a.area_id=b.TID')
					->limit($this->page->firstRow,$this->page->listRows)
					->where($info)->select();
		$this->assign('page',$this->page->show());
		$this->assign('list',$list);
		$this->display('Text/list');
	}
	
	public function text_edit(){
		$db_text=M('text');
		$db_area=M('area');
		
		$TID=intval($_GET['TID']);
		if($TID>0){
			$info=$db_text->field('TID,title,content,area_id')->find($TID);
			$this->assign('info',$info);
		}
		$area = $db_area->field('TID,area_name')->select();
		$this->assign('area',$area);
		$this->display('Text/edit');
	}
	
	public function text_save(){
		$info=array(
			'title'=>trim($_POST['title']),
			'content'=>trim($_POST['content']),
			'area_id'=>intval($_POST['area_id']),
			'EditTime'=>$_SERVER['REQUEST_TIME']
		);
		$TID=intval($_POST['TID']);
		$db_text=M('text');
		
		if($TID>0){
			$info['TID']=$TID;
			if($db_text->save($info)!==false){
				$this->success('修改成功',U('text_list'));die;
			}else{
				$this->error('修改失败');die;
			}
		}else{
			$info['AddTime']=$_SERVER['REQUEST_TIME'];
			if($db_text->add($info)>0){
				$this->success('添加成功',U('text_list'));die;
			}else{
				$this->error('添加失败');die;
			}
		}
	}
	
	public function text_del(){
		$TID=intval($_GET['TID']);
		$db_text=M('text');
		if($db_text->delete($TID)!==false){
			$this->success('删除成功',U('text_list'));die;
		}else{
			$this->error('删除失败');die;
		}
	}
//eof 文章管理

//bof 商品档案管理
	public function goods_data_list(){
		if($_GET['search']){
			if(!empty($_GET['title'])){
				$where['a.title']=array('like','%'.trim(urldecode($_GET['title'])).'%');
				$sea['title']=trim(urldecode($_GET['title']));
			}
			if(intval($_GET['type'])!='0'){
				$where['a.type']=intval($_GET['type']);
				$sea['type']=intval($_GET['type']);
			}
			$sea['search']=trim($_GET['search']);
		}
		if($_GET['creat_table']){
			$this->goods_data_excel();
		}
		$res = array(
		            'db' => array('name'=>'goods_data',
		                         'field'=>'TID,title,logo,type,sell_info,def_mark_price,def_seller_price,remarks,percentage',
		                        'join'=>'',
		                        'order'=>'a.AddTime desc',
		                        'where'=>$where),
		            'page'=>$sea,
		            'display' => 'GoodsData/list'
		    );

		$this->assign('sea',$sea);	
		$this->assign('type',C('GOODS_TYPE'));
		
		$this->db_list($res);
	}
	public function goods_data_edit(){
		$db = M('goods_data');
		$TID = intval($_GET['TID']);
		if($TID>0){
			$info=$db->field('TID,title,mark,logo,type,sell_info,freight,cate,banner,score,product_info,detail,def_mark_price,def_seller_price,percentage,remarks')
					->find($TID);
			if(!empty($info['banner'])){
				$info['banner']=explode('||',$info['banner']);
			}
		}
		//属性获取
		$cate = $this->cate_ergodic_v();
		
		$this->assign('type',C('GOODS_TYPE'));		
		$this->assign('cate',$cate);
		$this->assign('info',$info);
		$this->display('GoodsData/edit');
	}
	
	public function goods_data_save(){
//		dump($_POST);die;
		$db_data = M('goods_data');
		$info = array(
					'title'=>trim($_POST['title']),
					'mark'=>trim($_POST['mark']),
					'type'=>intval($_POST['type']),
					'sell_info'=>trim($_POST['sell_info']),
					'score'=>intval($_POST['score']),
					'def_mark_price'=>doubleval($_POST['def_mark_price']),
					'def_seller_price'=>doubleval($_POST['def_seller_price']),
					'percentage'=>doubleval($_POST['percentage']),
					'product_info'=>trim($_POST['product_info']),
					'detail'=>trim($_POST['detail']),
					'remarks'=>trim($_POST['remarks']),
					'freight'=>intval($_POST['freight']),
					'EditTime'=>$_SERVER['REQUEST_TIME']
		);
		if(!empty($_POST['cate'])){
			$info['cate']='0,'.implode(',',$_POST['cate']).',0';
		}
		if(!empty($_POST['banner'])){
			$info['banner']=implode('||',$_POST['banner']);
		}
		//保存图片
		$upload = $this->upload('Goods/');		
		if(!empty($upload)){
			foreach($upload as $file){
//				echo $file['key'];die;
				if(empty($info['logo'])){
					$info['logo'] = $this->rootpath.$file['savepath'].$file['savename'];
				}else{
					$info['logo'].='||'.$this->rootpath.$file['savepath'].$file['savename'];
				}
			}
		}
//		print_r($info);die;	
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID']=$TID;
			$old = $db_data->field('logo')->find($TID);
			if(!empty($info['logo'])){
				$this->file_del($old['logo']);
			}
			if($db_data->save($info)!==false){
				$this->success('修改成功',U('goods_data_list'));die;
			}else{
				$this->error('修改失败');die;
			}	
		}else{
			$info['AddTime']=$_SERVER['REQUEST_TIME'];
			if($db_data->add($info)>0){
				$this->success('添加成功',U('goods_data_list'));die;
			}else{
				$this->error('添加失败');die;
			}
		}
	}

	public function goods_data_del(){
		$res = array(
		            'db' => array('name'=>'goods_data'),
		            'file'=>array('logo','banner'),
		            'edit'=>'',
		            'success'=>array('mes'=>'删除成功','url'=>'goods_data_list'),
		            'error'=>array('mes'=>'删除失败','url'=>'goods_data_list')
		        );
		$this->db_del($res);
	}
	
	public function goods_data_excel(){
		$db_goods=M('goods_data');
		$db_cate = M('category');
		
		if(!empty($_GET['title'])){
			$where['a.title']=array('like','%'.trim($_GET['title']).'%');
			$sea['title']=trim($_GET['title']);
		}
		if($_GET['type']!='0'){
			$where['a.type']=intval($_GET['type']);
			$sea['type']=intval($_GET['type']);
		}		
		
		$list = $db_goods->alias('a')
					->field('a.title,a.mark,a.cate,sell_info,a.score,a.def_mark_price,a.def_seller_price,a.percentage,a.remarks,a.product_info,a.freight,b.type')
					->join('zp_seller_type b on a.type=b.TID')
					->where($where)->select();
		foreach($list as $k=>$v){
			$ca='';
			
			$cate = $db_cate->field('cate_name cate')->where('TID in ('.$v['cate'].')')->select();
			foreach($cate as $va){
				if(empty($ca)){
					$ca.=$va['cate'];
				}else{
					$ca.='、'.$va['cate'];
				}
			}
			$list[$k]['cate']=$ca;
			$list[$k]['freight']=$v['freight']==0?'否':'是';
		}
		
//		dump($list);die;
		vendor('PHPExcel.PHPExcel');
		// Create new PHPExcel object 创建excel类
		$objPHPExcel = new \PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		
		
		// Add some data
		//$objPHPExcel->setActiveSheetIndex(0);
		
		//excel表格从1开始计数,数组从0计数
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','商品名称');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','补充说明');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','分类');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','销售类型');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','默认市场价');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','默认购买价');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','提成');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','空瓶价值');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','产品信息');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J1','销售信息');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K1','运费');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1','备注');
		//合并单元格
//		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');
		
//		//填充数据
		foreach($list as $key=>$ex_item){
			$num=$key+2;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $ex_item['title']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $ex_item['mark']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $ex_item['cate']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $ex_item['type']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$num, $ex_item['def_mark_price']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$num, $ex_item['def_seller_price']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$num, $ex_item['percentage']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $ex_item['score']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $ex_item['product_info']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $ex_item['sell_info']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $ex_item['freight']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$num, $ex_item['remarks']);
		}
		
		// Rename worksheet(重命名表格)
		$objPHPExcel->getActiveSheet()->setTitle('商品档案');
		
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="商品档案列表.'.date('Y-m-d').'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;		
	}
//eof 商品档案管理


//bof 数据库操作
	private function db_set($res){
		$db = M($res['db']);
		$db->where('TID='.$res['TID'])->setField($res['name'],''.$res['value']);
//		echo $db->getLastSql();die;
		redirect($_SERVER['HTTP_REFERER']);die;
	}
	public function db_list($res){
		//分页显示
		$db = M($res['db']['name']);

		
		$where='';
		if(!empty($res['db']['where'])){
			$where=$res['db']['where'];			
		}

		$count = $db->alias('a')->where($where)->count(0);	
		$this->page_set($count,15,'种分类',$res['page']);
	//	echo $db->getLastSql();die;			
		$info = $db->alias('a')
					->field($res['db']['field'])->where($where)
					->join($res['db']['join'])
					->limit($this->page->firstRow,$this->page->listRows)
					->order($res['db']['order'])->select();
//		echo $db->getLastSql();die;
		//附加配置文件
		foreach($res['resource'] as $k=>$re){
			$res = $this->$re();
//			echo $k;die;
//			dump($res);die;
			$this->assign($k,$res);
		}
		
		//dump($info);die;
		$this->assign('list',$info);
		$this->assign('page',$this->page->show());				
		$this->display($res['display']);
	}
	
	public function db_no_page_list($res){
		//分页显示
		$db = M($res['db']['name']);

		$where='';
		if(!empty($res['db']['where'])){
			$where=$res['db']['where'];			
		}
		//		echo $db->getLastSql();die;			
		$info = $db->alias('a')
					->field($res['db']['field'])
					->where($where)
					->join($res['db']['join'])
					->order($res['db']['order'])->select();
//		echo $db->getLastSql();die;
		//附加配置文件
		foreach($res['resource'] as $k=>$re){
			$res = $this->$re();
//			echo $k;die;
//			dump($res);die;
			$this->assign($k,$res);
		}
		
//		dump($info);die;
		$this->assign('list',$info);
		$this->display($res['display']);
	}	
	
	public function db_edit($res){
		$db = M($res['db']['name']);
		$TID = intval($_GET['TID']);
		if($TID>0){
			$info = $db->alias('a')->field($res['db']['field'])->join($res['db']['join'])->where('a.TID='.$TID)->find();
			$actionName='修改';
//			dump($info);die;
			$this->assign('info',$info);
		}else{
			$actionName='添加';
		}
//		echo $actionName;die;
//		echo $db->getLastSql();die;
//		print_r($info);die;
		$this->assign('actionName',$actionName);
		//附加配置文件
//		print_r($res['resource']);die;
		foreach($res['resource'] as $k=>$re){
			$res = $this->$re();
//			echo $k;die;
//			dump($res);die;
			$this->assign($k,$res);
		}
		$this->display($res['display']);
	}
	
	public function db_save($res){
		$db = M($res['db']['name']);
		
		$info =array('EditTime'=>$_SERVER['REQUEST_TIME']);
//		dump($_POST);die;
		foreach($res['db']['field'] as $field){
			if(method_exists($this,$field['check'])){
				$info[$field['name']] = $this->$field['check']($field['name']);
			}else{
				$info[$field['name']] = $field['check']($_POST[$field['name']]);
			}
		}
		if(!empty($res['db']['filefield'])){
		//保存图片
		$upload = $this->upload($res['fileload']);		
			if(!empty($upload)){
				foreach($upload as $file){
	//				echo $file['key'];die;
					if(in_array($file['key'],$res['db']['filefield'])){
						if(empty($info[$file['key']])){
							$info[$file['key']] = $this->rootpath.$file['savepath'].$file['savename'];
						}else{
							$info[$file['key']].='||'.$this->rootpath.$file['savepath'].$file['savename'];
						}
					}else{
						$this->error('上传了不合适的文件类型');die;					
					}
				}
			}
		}
//		print_r($info);die;	
		$TID = intval($_POST['TID']);
		if($TID>0){
			$info['TID'] = $TID;
			foreach($res['db']['filefield'] as $file){
				if(!empty($info[$file])){
					$old = $db->where('TID='.$TID)->getField($file);
					$this->file_del($old);
				}
			}
			if($db->save($info)!==false){
				$this->success($res['success']['mes'],U($res['success']['url']));
			}else{
				$this->success($res['success']['mes'],U($res['success']['url']));
			}
		}else{
			$info['AddTime']=$info['EditTime'];
			if($db->add($info)>0){
				$this->success($res['success']['mes'],U($res['success']['url']));
			}else{
				$this->success($res['success']['mes'],U($res['success']['url']));
			}				
		}
	}
	
	public function db_del($res){
		$db=M($res['db']['name']);
		$TID = intval($_GET['TID']);
		if($TID>0){
			foreach($res['file'] as $file){
				$old = $db->where('TID='.$TID)->getField($file);
				$this->file_del($old);
			}
			if(!empty($res['edit'])){
				$old = $db->field($res['edit'])->find($TID);
				foreach($old as $file){
					preg_match_all ( "|Uploadfile/temp/[0-9]{4}-[0-9]{2}-[0-9]{2}/[0-9]*_[0-9]*\.[a-zA-Z]{3,4}|", $file,$content );					
					$this->file_del($content[0]);
				}
			}
			if($db->delete($TID)!==false){
//				echo $db->getLastSql();die;
				$this->success($res['success']['mes'],U($res['success']['url']));
			}else{
//				echo $db->getLastSql();die;
				$this->error($res['error']['mes'],U($res['error']['url']));
			}
		}else{
			$this->success($res['success']['mes'],U($res['success']['url']));
		}
	}
//eof 数据库操作

}
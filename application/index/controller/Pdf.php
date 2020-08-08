<?php
		namespace app\index\controller;
		use think\Controller;
		use think\request;
		use think\Loader;
	
		
		class Pdf extends controller
		{
			//系统登录
			public function login()
			{
				return view('login');
			}
			//登录处理
			public function go()
			{
				$data = $_POST;
				$phone=addslashes(htmlspecialchars($data['phone']));
				$pwd=addslashes(htmlspecialchars($data['password']));
				$arr = Db("admin")->where('phone',$phone)->find();
				if($phone!=$arr['phone']){
					return json(['code' => "1001",'msg'=>"没有该用户!"]);
				}else if($pwd!=$arr['password']){
					return json(['code' => "1001",'msg'=>"密码错误!"]);
				}else{
					return json(['code' => "200",'msg'=>"登录成功！"]);
				}

			}
			//首页-系统管理
			public function profile()
			{
				return view('profile');
			}
            //首页-用户管理
			public function index()
			{
			   $data=Db('user')->select();
			   //var_dump($data);die;
   	           return $this->fetch('yonghu',['data'=>$data]);
				return view('index');
				//return view('yonghu');
			}
			//添加用户
			public function add()
			{
				$data['phone']=$_POST['phone'];
                $data['name']=$_POST['name'];
                $data['type']=$_POST['type'];
                $data['emal']=$_POST['emal'];
                if(empty($data['phone']) || empty($data['name']) || empty($data['type']) || empty($data['emal']) ){
                	return json(['code' => "1001",'msg'=>"数据不能为空!"]);
                }
				$request = Db("user")->insert($data);
				if($request){
					return json(['code' => "200",'msg'=>"添加成功!"]);
				}else{
					return json(['code' => "1001",'msg'=>"添加失败!"]);
				}
			}
			//查询
			public function select()
			{
				$cont = $_POST['cont'];
				$type = $_POST['type'];
				if(empty($cont)){
					$res = Db('user')->where('type',$type)->select();
					if($res){
						return json(['code' => "200",'msg'=>"请等待数据展示!",'data'=>$res]);
					}
				}else{
					$where ['name'] = ['like',"%".$cont."%"];
					$data=Db('user')->where($where)->select();
					if($data){
						return json(['code' => "200",'msg'=>"请等待数据展示!",'data'=>$data]);
					}else{
						return json(['code' => "1001",'msg'=>"没有该用户!"]);
					}

				}

				

			}
			//导出
			public function out()
			{
				$cont = $_GET['cont'];
				$type = $_GET['type'];
				
				if(empty($cont)){
					$res = Db('user')->where('type',$type)->select();
					if(!empty($res)){
						
						return json(['code' => "200",'msg'=>"导出成功!",'data'=>$res]);
					}
				}else{
					$where ['name'] = ['like',"%".$cont."%"];
					$data=Db('user')->where($where)->select();
					if(!empty($data)){
                       
					   return json(['code' => "200",'msg'=>"导出成功!",'data'=>$data]);
					}

				}


			}
			//导出excel
		    public function ExportExcel($datas){
		        for ($i=0; $i < count($datas) ; $i++) {
		        //拼接数据，形成新的数组，用来装新导出的数据
		            $data[$i]['id'] = $datas[$i]["id"];
		            $data[$i]['name'] = $datas[$i]["name"];
		            if($datas[$i]["type"]==1){
		                $data[$i]['type'] = "vip用户";
		            }else{
		                $data[$i]['type'] = "普通用户";
		            }
		            $data[$i]['phone'] = $datas[$i]["phone"];
		            $data[$i]['emal'] = $datas[$i]["emal"];
		        }
		 
		        try {
                 
		            //引用类包   不需要修改
		            Loader::import('PHPExcel.PHPExcel');
		            Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');
		 
		            //实例化类包 不用改
		          
		            vendor("PHPExcel.PHPExcel");
		            $objPhpExcel=new \PHPExcel();
		            
		            //所有单元格进行垂直和水平居中设置  不用改
		            $objPhpExcel ->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		            $objPhpExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		            
		 
		            /*
		             设置表头标题，这里根据自己实际数据的需求写
		            */
		            $rowVal = array(
		                0=>'编号',
		                1=>'姓名',
		                2=>'分类',
		                3=>'电话',
		                4=>'邮箱'
		                
		            );
		 
		 
		            foreach ($rowVal as $k=>$r){
		                $objPhpExcel
		                ->getActiveSheet()
		                ->getStyleByColumnAndRow($k,1)
		                ->getFont()->setBold(true);//字体加粗
		                $objPhpExcel
		                ->getActiveSheet()
		                ->getStyleByColumnAndRow($k,1)
		                ->getAlignment()
		                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
		               $objPhpExcel
		                ->getActiveSheet()
		                ->setCellValueByColumnAndRow($k,1,$r);
		 
		            }
		 
		             //设置当前的sheet索引 用于后续内容操作 
		            $objPhpExcel->setActiveSheetIndex(0);
		            $objActSheet=$objPhpExcel->getActiveSheet();
		 
		            //设置表格的宽度  根据情况修改
		            $objActSheet->getColumnDimension('A')->setWidth(10);//编号
		            $objActSheet->getColumnDimension('B')->setWidth(30);//姓名
		            $objActSheet->getColumnDimension('C')->setWidth(30);//分类
		            $objActSheet->getColumnDimension('D')->setWidth(30);//电话           
		            $objActSheet->getColumnDimension('E')->setWidth(10);//邮箱
		         
		 
		             /*
		            设置Excel表的名称
		             */
		            $title="用户表";//
		            $objActSheet->setTitle($title);
		 
		            //设置单元格内容
		            //var_dump($data);die;
		            foreach($data  as $k => $v)
		            {
		               $num=$k+2;
		               $objPhpExcel ->setActiveSheetIndex(0)
		               //Excel的第A列，id是你查出数组的键值，下面以此类推
		               ->setCellValue('A'.$num, $v['id'])/*编号*/
		               ->setCellValue('B'.$num, $v['name'])/*用户名*/
		               ->setCellValue('C'.$num, $v['type'])/*分类*/
		               ->setCellValue('D'.$num, $v['phone'])/*电话*/               
		               ->setCellValue('E'.$num, $v['emal']);/*邮箱*/
		 
		                $name=date('Y-m-d');//设置文件名
		 
		           }
		           // header('content-Type:application/vnd.ms-excel;charset=utf-8');
		                header("Content-Type: application/force-download");
		                header("Content-Type: application/octet-stream");
		                header("Content-Type: application/download");
		                header("Content-Transfer-Encoding:utf-8");
		                header("Pragma: no-cache");
		                header('Content-Type: application/vnd.ms-excel');
		                header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
		                header('Cache-Control: max-age=0');
		                $objWriter = \PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
		                $objWriter->save('php://output');
		                		 
		            
		 
		        } catch (Exception $e) {
		            $this->error('操作异常');
		        }
		    }
		 


			 //首页-广告管理
			public function blank()
			{
				return view('blank');
			}
			//首页-团队介绍
			public function themifyicon()
			{
				return view('themifyicon');
			}

			//404页面
			public function not()
			{
				$res = json_decode($_GET['name'],true);				
				$this->ExportExcel($res);
				return view('not');
			}

			
		
		}


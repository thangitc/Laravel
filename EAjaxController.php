<?php
class EAjaxController extends Controller
{
	public $layout = false;
	/*HungNT*/
     public function actionAddDownload()
    {
        $id=isset($_POST["id"]) ? intval($_POST["id"]):0;
        $dow=EDoc::upDow($id);                    
    }
    public function actionAddSuggest()
    {
        $service_id=isset($_POST["service_id"]) ? intval($_POST["service_id"]):0;
        $fullname= isset($_POST['fullname']) ? trim(strip_tags($_POST['fullname'])) : '';
        $email= isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
        $mobile= isset($_POST['mobile']) ? trim(strip_tags($_POST['mobile'])) : '';
        $description= isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : '';
        $error='';
        if($email=='')
        {
            $error.='Vui lòng nhập địa chỉ email!<br>';
        }
        else if(!Common::validateEmailSyntax($email))
        {
            $error.='Email không hợp lệ!<br>';
        }
        if($fullname=='')
        {
            $error.='Vui lòng nhập họ tên đầy đủ!<br>';
        }
        if($description=='')
        {
            $error.='Vui lòng nhập nội dung góp ý!<br>';
        }else { 
        $array_input=array('service_id'=>$service_id,'fullname'=>mysql_escape_string($fullname),'email'=>mysql_escape_string($email),'description'=>mysql_escape_string($description),'status'=>1,'mobile'=>$mobile,'create_date'=>time());
        $s=CommonModel::insertObject($array_input,'e_suggest');      
        if($s!=0)
            {              
                $error='Góp ý thành công. Cảm ơn bạn !';               
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý';
            } 
        }      
        $output=array('error'=>$error);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;             
    }
    public function actionAddQa()
    {        
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
        $username=isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $description=isset($_POST['description']) ? $_POST['description']:'';
        $parent_id=isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $error='';
        $status=0;
        if(!isset($_SESSION['userid']))
        {
            $error.='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            $output=array('error'=>$error,'status'=>$status);
            $output=json_encode($output);
            header("Content-Type: application/json; charset=utf-8");
            echo $output;
            exit();
        }
        if($description=='')
        {
            $error.='Vui lòng nhập nội dung câu hỏi, trả lời.<br>';
        }
        if($error=='')
        {
            $create_date=time();
            $edit_date=$create_date;
            $publish_date=$create_date;
            
            $array_input=array('parent_id'=>$parent_id,'description'=>mysql_escape_string($description),'user_id'=>$user_id,'username'=>mysql_escape_string($username),'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date);
            $ok = CommonModel::insertObject($array_input,'e_qa');
            if($ok>=0)
            {
                $status=1;
                $error='Gửi thành công! Cảm ơn bạn!';
				//Activty
				/*
				$users = $this->users;
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['username']));
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa gửi câu hỏi lên Tuyensinh247</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
				*/
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
    public function actionLikeQa()
    {
        if(!isset($_SESSION['userid']))
        {
            $error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            echo $error;
            exit();
        }
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$users = $this->users;
		$username = $users['username'];
		$fbid = $users['fbid'];
		$avatar = $users['avatar'];
        $qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
        $is_like='like_'.$qa_id.$user_id;
        if(!isset($_COOKIE[$is_like]))
        {
            $expire=time()+3600;
            setcookie($is_like,$is_like,$expire,'/');
            $ok=EQa::updateQaLike($qa_id);
			$array_input = array('user_id'=>$user_id,'username'=>$username,'fbid'=>$fbid,'avatar'=>mysql_escape_string($avatar),'qa_id'=>$qa_id,'create_date'=>time());
			CommonModel::insertObject($array_input,'e_qa_like');
            if($ok>=0) echo 'Like thành công!';
        }
        else
        {
            echo 'Bạn đã Like câu này rồi!';
        }
        exit();
    }
    public function actionPostQa()
    {        
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
        $username=isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $description=isset($_POST['description']) ? $_POST['description']:'';
		$html_img=isset($_POST['html_img']) ? $_POST['html_img']:'';
		$parent_id=isset($_POST['parent_id']) ? intval($_POST['parent_id']): 0;
        $error='';
        $status=0;
        if(!isset($_SESSION['userid']))
        {
            $error.='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            $output=array('error'=>$error,'status'=>$status);
            $output=json_encode($output);
            header("Content-Type: application/json; charset=utf-8");
            echo $output;
            exit();
        }
		$users = $this->users;
		$avatar = $users['avatar'];
		$fbid = $users['fbid'];
        if($description=='' && $html_img=='')
        {
            $error.='Vui lòng nhập nội dung câu hỏi, trả lời.<br>';
        }
		$description = preg_replace('/http:\/\/(.*?)\s|http:\/\/(.*?)\n|http:\/\/(.*?)/si','<a href="http://$1">http://$1</a> ',$description);
        if($error=='')
        {
            $create_date=time();
            $edit_date=$create_date;
            $publish_date=$create_date;
			$sub_id = '';
			$cat_id = 0;
			$row_parent = array();
            if($parent_id==0)
			{
				$level = 1;
			}
			else
			{
				$row_parent = EQa::getQaById2($parent_id);
				$level_parent = $row_parent['level'];
				$level = $level_parent+1;
				//$sub_id = $row_parent['parent_id'];
				$cat_id = $row_parent['cat_id'];
			}
            $array_input=array('description'=>mysql_escape_string($description),'user_id'=>$user_id,'username'=>mysql_escape_string($username),'avatar'=>mysql_escape_string($avatar),'fbid'=>$fbid,'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date, 'parent_id'=>$parent_id,'level'=>$level,'cat_id'=>$cat_id);
            $qa_id = CommonModel::insertObject($array_input,'e_qa');
            if($qa_id>=0)
            {
                $status=1;
                $error='Gửi thành công! Cảm ơn bạn!';
				//Them anh
				$sub_sql = '';
				preg_match_all('/alt=["](.*?)["]/si',$html_img, $matches);
				if(isset($matches[1]))
				foreach($matches[1] as $value)
				{
					$value = mysql_escape_string(trim(strip_tags($value)));
					$sub_sql .= '("'.$qa_id.'", "'.$value.'", "'.$create_date.'"),';
				}
				$sub_sql = rtrim($sub_sql,',');
				if($sub_sql!='')
				{
					EQa::insertImgQa($sub_sql);
				}
				//Cap nhat sub_id cho chinh no
				CommonModel::updateObject(array('sub_id'=>$qa_id),'id',$qa_id,'e_qa');
				//Cap nhat sub_id cho cha
				if(!empty($row_parent))
				{
					if($row_parent['level']==1)
					{
						$sub_id_2 = $row_parent['sub_id'].','.$qa_id;
						CommonModel::updateObject(array('sub_id'=>$sub_id_2),'id',$row_parent['id'],'e_qa');
					}
					if($row_parent['level']==2)
					{
						//Cap nhat cho thang cha thu nhat
						$sub_id_2 = $row_parent['sub_id'].','.$qa_id;
						CommonModel::updateObject(array('sub_id'=>$sub_id_2),'id',$row_parent['id'],'e_qa');
						//Cap nhat cho thang cha thu 2 (cha goc)
						$row_parent_3 = EQa::getQaById2($row_parent['parent_id']);
						$sub_id_3 = $row_parent_3['sub_id'].','.$qa_id;
						CommonModel::updateObject(array('sub_id'=>$sub_id_3),'id',$row_parent_3['id'],'e_qa');
					}
				}
				//Activty
				$users = $this->users;
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['username']));
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa gửi câu hỏi lên Tuyensinh247</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
    
    public function actionEditTeacher()
    {        
        $regency_id=isset($_POST['regency_id']) ? intval($_POST['regency_id']) : 0;
        $teacher_id=isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
        $is_mobile=isset($_POST['is_mobile']) ? intval($_POST['is_mobile']) : 0;
        $is_facebook=isset($_POST['is_facebook']) ? intval($_POST['is_facebook']) : 0;
        $is_website=isset($_POST['is_website']) ? intval($_POST['is_website']) : 0;
        $title=isset($_POST['title']) ? $_POST['title']:'';
        $email=isset($_POST['email']) ? $_POST['email']:'';
        $mobile=isset($_POST['mobile']) ? $_POST['mobile']:'';
        $facebook=isset($_POST['facebook']) ? $_POST['facebook']:'';
        $website=isset($_POST['website']) ? $_POST['website']:'';
        $list_id=isset($_POST['list_id']) ? $_POST['list_id']:'';
        $school=isset($_POST['school']) ? $_POST['school']:'';
        $description=isset($_POST['description']) ? $_POST['description']:'';
        $introtext=isset($_POST['introtext']) ? $_POST['introtext']:'';
        $avatar=isset($_POST['avatar']) ? $_POST['avatar']:'';
        

        $error='';
        $status=0;
        if($title=='')
        {
            $error.='Vui lòng nhập họ tên.<br>';
        }
        if($email=='')
        {
            $error.='Vui lòng nhập email.<br>';
        }
        if($description=='')
        {
            $error.='Vui lòng nhập giới thiệu chi tiết.<br>';
        }
        
        if($error=='')
        {
            
            $array_input=array('description'=>mysql_escape_string($description),'regency'=>$regency_id,'is_mobile'=>$is_mobile,'is_facebook'=>$is_facebook,'is_website'=>$is_website,'title'=>$title,'alias'=>Common::generate_slug($title),'email'=>mysql_escape_string($email),'mobile'=>mysql_escape_string($mobile),'facebook'=>mysql_escape_string($facebook),'website'=>mysql_escape_string($website),'subject'=>mysql_escape_string($list_id),'school'=>mysql_escape_string($school),'introtext'=>mysql_escape_string($introtext),'avatar'=>$avatar);
            $array_input2=array('description'=>mysql_escape_string($description),'fullname'=>$title,'email'=>mysql_escape_string($email),'mobile'=>mysql_escape_string($mobile),'avatar'=>$avatar);
            $ok=CommonModel::updateObject($array_input,'id',$teacher_id,'e_teacher');
            $ok1=CommonModel::updateObject($array_input2,'teacher_id',$teacher_id,'e_user');
            if($ok>=0 && $ok1>=0)
            {
                $status=1;
                $error='Cập nhật thành công! Cảm ơn bạn!';
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
    public function actionAddQaTeacher()
    {        
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
        $username=isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $description=isset($_POST['description']) ? $_POST['description']:'';
        $teacher_id=isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
        $parent_id=isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $error='';
        $status=0;
        if(!isset($_SESSION['userid']))
        {
            $error.='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            $output=array('error'=>$error,'status'=>$status);
            $output=json_encode($output);
            header("Content-Type: application/json; charset=utf-8");
            echo $output;
            exit();
        }
        if($description=='')
        {
            $error.='Vui lòng nhập nội dung câu hỏi, trả lời.<br>';
        }
        if($error=='')
        {
            $create_date=time();
            $edit_date=$create_date;
            $publish_date=$create_date;
            
            $array_input=array('teacher_id'=>$teacher_id,'parent_id'=>$parent_id,'description'=>mysql_escape_string($description),'user_id'=>$user_id,'username'=>mysql_escape_string($username),'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date);
            $ok = CommonModel::insertObject($array_input,'e_qa_teacher');
            if($ok>=0)
            {
                $status=1;
                $error='Gửi thành công! Cảm ơn bạn!';
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
    public function actionLikeQaTeacher()
    {
        if(!isset($_SESSION['userid']))
        {
            $error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            echo $error;
            exit();
        }
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
        $qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
        $is_like='like_'.$qa_id.$user_id;
        if(!isset($_COOKIE[$is_like]))
        {
            $expire=time()+3600;
            setcookie($is_like,$is_like,$expire,'/');
            $ok=EQaTeacher::updateQaLike($qa_id);
            if($ok>=0) echo 'Like thành công!';
        }
        else
        {
            echo 'Bạn đã Like câu này rồi!';
        }
        exit();
    }
    public function actionLoadTop5()
	{
		$cat_id= isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;         
		$this->renderPartial("application.views.eWidget.top5",
		array('cat_id'=>$cat_id
		));            
	}
    public function actionLoadDistrict()
	{
		$city_id = isset($_POST["city_id"]) ? intval($_POST["city_id"]) : 0;
		$district_id = isset($_POST["district_id"]) ? intval($_POST["district_id"]) : 0;
		/* Lấy danh sách các quan huyen */
		$arr_district = array("cit_parent_id"=>$city_id);
		$district = ECity::getDistrictByCity($city_id);
		$html='<option>Quận/Huyện</option>';
		foreach($district as $row)
		{
			if($row['cit_id']==$district_id)
			{
				$html.='<option selected value='.$row['cit_id'].'>'.$row['cit_name'].'</option>';
			}
			else
			{
				$html.='<option value='.$row['cit_id'].'>'.$row['cit_name'].'</option>';
			}
		}
		echo $html;
	}
    public function actionLoadSchool()
	{
		$city_id = isset($_POST["city_id"]) ? intval($_POST["city_id"]) : 0;
		$district_id = isset($_POST["district_id"]) ? intval($_POST["district_id"]) : 0;
		$level_id = isset($_POST["level_id"]) ? intval($_POST["level_id"]) : 0;
        $school_id = isset($_POST["school_id"]) ? intval($_POST["school_id"]) : 0;
		$level_number_id = isset($_POST["level_number_id"]) ? intval($_POST["level_number_id"]) : 0;
        if($level_number_id==10 || $level_number_id==11 || $level_number_id==12 || $level_number_id==13){
            $level_id=2;
        }else if($level_number_id==6 || $level_number_id==7 || $level_number_id==8 || $level_number_id==9){
            $level_id=1;
        }
		/* Lấy danh sách các truong */
		$school = ESchool::getSchoolByCity($city_id,$district_id,$level_id);
		//var_dump($city_id);exit();
		$html='';
		$html.='<option value=99999>Chọn trường đang và đã học tập</option>';
		foreach($school as $row)
		{
			$select='';
			if($row['id']==$school_id) $select="selected";            
			$html.='<option '.$select.' value='.$row['id'].'>'.$row['title'].'</option>';             
		}
        $html.='<option value=0>Trường khác</option>';  
		
		echo $html;
	}
	public function actionEditUser()
    {        
        $user_id=isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $is_mobile=isset($_POST['is_mobile']) ? intval($_POST['is_mobile']) : 0;
        $is_facebook=isset($_POST['is_facebook']) ? intval($_POST['is_facebook']) : 0;
        $school_id=isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
        $city_id=isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $district_id=isset($_POST['district_id']) ? intval($_POST['district_id']) : 0;
        $school_level=isset($_POST['school_level']) ? intval($_POST['school_level']) : 0;
        $level_number=isset($_POST['level_number']) ? intval($_POST['level_number']) : 0;
        $sex=isset($_POST['sex']) ? intval($_POST['sex']) : 0;
        $fullname=isset($_POST['fullname']) ? $_POST['fullname']:'';
        $school_name=isset($_POST['school_name']) ? $_POST['school_name']:'';
        $email=isset($_POST['email']) ? $_POST['email']:'';
        $mobile=isset($_POST['mobile']) ? $_POST['mobile']:'';
        $facebook=isset($_POST['facebook']) ? $_POST['facebook']:'';
        $orginal_school=isset($_POST['orginal_school']) ? $_POST['orginal_school']:'';
        $avatar=isset($_POST['avatar']) ? $_POST['avatar']:'';
        $day=isset($_POST['day'])? intval($_POST['day']):0;
        $month=isset($_POST['month'])? intval($_POST['month']):0;
        $year=isset($_POST['year'])? intval($_POST['year']):0;
        $date=0;
        if($day!=0 && $month!=0 && $year!=0)
        {
            $date = mktime(0,0,0,$month,$day,$year);
        }
        $city_name=''; 
        $district_name='';
        if($city_id!=0){
            $city=ECity::getCityById($city_id);
            $city_name=$city['cit_name'];    
        }
        if($district_id!=0){
            $district=ECity::getCityById($district_id);
            $district_name=$district['cit_name'];    
        }  
        

        $error='';
        $status=0;
        if($fullname=='')
        {
            $error.='Vui lòng nhập họ tên.<br>';
        }
        if($email=='')
        {
            $error.='Vui lòng nhập email.<br>';
        }
         if($school_id!=0)
        {
            $orginal_school='';
        }
        
        
        if($error=='')
        {
            
            $array_input=array('is_mobile'=>$is_mobile,'is_facebook'=>$is_facebook,'school_id'=>$school_id,'city_id'=>$city_id,'district_id'=>$district_id,'level_school'=>$school_level,'level_number'=>$level_number,'sex'=>$sex,'email'=>mysql_escape_string($email),'mobile'=>mysql_escape_string($mobile),'facebook'=>mysql_escape_string($facebook),'fullname'=>mysql_escape_string($fullname),'orginal_school'=>mysql_escape_string($orginal_school),'avatar'=>$avatar,'city_name'=>$city_name,'district_name'=>$district_name,'school_name'=>$school_name,'birthday'=>$date);
            $ok=CommonModel::updateObject($array_input,'id',$user_id,'e_user');
            if($ok>=0)
            {
                $status=1;
                $error='Cập nhật thành công! Cảm ơn bạn!';
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
    
    //update thong tin user
    
    public function actionCheckUsername1()
     {                                 
         $user= $_POST['user_name'];
         $data=EUser::getUserByUsername($user);
         if (!empty($data))
         {
             echo "Tên đăng nhập đã tồn tại, bạn vui lòng chọn tên khác";             
             
         }else {echo "Tên đăng nhập hợp lệ";}                 
     }
	public function actionEditPassUser()
    {        
        $user_id=isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $pass=isset($_POST['pass']) ? $_POST['pass']:'';
        $rePass=isset($_POST['rePass']) ? $_POST['rePass']:'';
        $passMd5=Common::genPass($pass);
        $password=Common::genPass($rePass);
        $user=EUser::getUserById($user_id);
                
        $error='';
        $status=0;
        if($pass=='')
        {
            $error.='Vui lòng nhập mật khẩu hiện tại.<br>';
        }
        if($rePass=='')
        {
            $error.='Vui lòng nhập mật khẩu mới.<br>';
        }
        if($passMd5!=$user['password'])
        {
            $error.='Mật khẩu hiện tại không đúng.<br>';
        }

        if($error=='')
        {
            
            $array_input=array('password'=>$password);
            $ok=CommonModel::updateObject($array_input,'id',$user_id,'e_user');
            if($ok>=0)
            {
                $status=1;
                $error='Cập nhật thành công! Cảm ơn bạn!';
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
	public function actionEditUsername()
    {        
        $user_id=isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $username=isset($_POST['reUsername']) ? $_POST['reUsername']:'';
        $rePass=isset($_POST['rePass']) ? $_POST['rePass']:'';
        $password=Common::genPass($rePass);
        $user=EUser::getUserById($user_id);
                
        $error='';
        $status=0;
        
        if($username=='')
        {
            $error.='Vui lòng nhập tên đăng nhập mới.<br>';
        }
        if($user['fbid']=='')
        {
            $error.='Bạn không có quyền đổi tên đăng nhập.<br>';
        }
        if($rePass=='')
        {
            $error.='Vui lòng nhập mật khẩu mới.<br>';
        }
        if($user['is_change_username']==1)
        {
            $error.='Bạn đã hết lượt đổi tên đăng nhập.<br>';
        }

        if($error=='')
        {
            
            $array_input=array('username'=>$username,'is_change_username'=>1,'password'=>$password);
            $ok=CommonModel::updateObject($array_input,'id',$user_id,'e_user');
            if($ok>=0)
            {
                $status=1;
                $error='Cập nhật thành công! Cảm ơn bạn!';
            }
            else
            {
                $error='Có lỗi trong quá trình xử lý!';
            }
        }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
    }
	public function actionCheckUsername()
    {        
        $username=isset($_POST['usernameNew']) ? trim($_POST['usernameNew']) :'';
        $user=EUser::getUserByUsername($username);      
        $error='';
        $status=0;     
         if($user)
         {
             $error="Tên đăng nhập đã tồn tại, bạn vui lòng chọn lại!";             
             
         }else {
             $status=1;
             $error='Tên đăng nhập hợp lệ!';
         }
        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;                 
         
    }
    public function actionReChargeCard()//Nap tien
    {
        $error='';
        $user_session_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']):0;//ID user
        if(!isset($_SESSION['userid']))
        {
            $error.='Bạn phải đăng nhập mới được thực hiện tính năng này!<br>';
            $output=array('error'=>$error,'status'=>0);
            $output=json_encode($output);
            header("Content-Type: application/json; charset=utf-8");
            echo $output;
            exit();
        }
        $users=$this->users;
        $card_code=isset($_POST['card_code']) ? $_POST['card_code']:'';
        $card_serial=isset($_POST['card_serial']) ? $_POST['card_serial']:'';
        $card_type=isset($_POST['card_type']) ? $_POST['card_type']:'viettel';
        $secure_code=isset($_POST['secure_code']) ? $_POST['secure_code']:'';
        if($card_code=='')
        {
            $error.='Vui lòng nhập mã thẻ cào!<br>';
        }
        if($card_type=='viettel' && $card_serial=='')
        {
            $error.='Vui lòng nhập Serial thẻ cào!<br>';
        }
        if($secure_code=='')
        {
            $error.='Vui lòng nhập mã bảo mật!<br>';
        }
        if($secure_code!='' && isset($_SESSION['secure_code']) && $_SESSION['secure_code']!=$secure_code)
        {
            $error.='Mã bảo mật không đúng!<br>';
        }
        if($error!='')
        {
            $output=array('error'=>$error,'status'=>0);
        }
        else
        {
            /*Nap tien*/
            $client =new SoapClient('http://192.168.1.127:8008/hdc_payment/hdc_pcard_service.php?wsdl');
            //$client =new SoapClient('http://210.211.97.6:8008/hdc_payment/hdc_pcard_service.php?wsdl');
            
            $username=$users['username'];
            $email_user =$users['email'];
            $card_code =$card_code;
            $card_seri= $card_serial;
            $provider_card=$card_type;
            $provider_payment='epay';
            $partner_id='';
            $service_type='ts247';
            $refcode='';
            $content_id=0;
            $mobile='';
            
            $result=$client->wsCard($username,$email_user,$mobile,$card_code,$card_seri,$provider_card,$provider_payment,$service_type,$partner_id,$refcode,$content_id);
            
            $resp=json_decode($result);
            $price=$resp->price;
            $response=$resp->response;
            $status=$resp->status; 
            
            if($status==1)
            {
                $ok = EAccount::updateMoneyByUserId($users['id'],$users['username'],$price);
                if($ok>=0)
                {
                    $array_input =array();
                    $array_input['user_id']=$users['id'];
                    $array_input['username']=strtoupper($users['username']);
                    $array_input['money']=$price;
                    if($card_type=='VTT')
                    { 
                        $array_input['telco']='viettel';
                        $array_input['money_telco']=($price/1000)*825;
                    }
                    else if($card_type=='VMS'){
                     $array_input['telco']='mobifone';
                     $array_input['money_telco']=($price/100)*85;
                    }
                    else if($card_type=='VNP'){
                     $array_input['telco']='vinaphone';
                     $array_input['money_telco']=($price/100)*85;
                    }
                    $array_input['type']=5;
                    $array_input['status']=1;
                    $array_input['introtext']='Nạp tiền vào tài khoản';
                    $array_input['create_time']= time();//date('Y-m-d H:i:s',time());
                    $array_input['create_date']= date('Y-m-d H:i:s');                    
                    $array_input['create_month']= date('m');                    
                    $array_input['create_year']= date('Y');                    
                    $array_input['create_day']= date('d');                    
                    CommonModel::insertObject($array_input,'e_transaction');
                    
                    $array_input2 =array();
                    $array_input2['price']=$price;
                    if($card_type=='VTT') $array_input2['telco']='viettel';
                    else if($card_type=='VMS') $array_input2['telco']='mobifone';
                    else if($card_type=='VNP') $array_input2['telco']='vinaphone';
                    $array_input2['code']=$card_code;
                    $array_input2['series']=$card_seri;
                    $array_input2['user_id']=$users['id'];
                    $array_input2['username']=strtoupper($users['username']);
                    $array_input2['create_date']=time();
                    CommonModel::insertObject($array_input2,'e_card_log');
                    
                    $error='Nạp tiền thành công!';
                    $status=1;
                }
                else
                {
                    $error='Nạp tiền không thành công! '.$response;
                    $status=0;
                }
            }
            else
            {
                $error='Nạp tiền không thành công! '.$response;
                $status=0;
            }
            $output=array('error'=>$error,'status'=>$status);
        }
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
        exit();
    }
    public function actionUploadDocument()
	{
		$error='';
		$status=0;
		$user_id=isset($_POST['user_id'])?$_POST['user_id']:0;
		$username=isset($_POST['username'])?$_POST['username']:'';
		$title=isset($_POST['title'])?$_POST['title']:'';
		$cat_id=isset($_POST['cat_id'])?$_POST['cat_id']:0;
		$file_doc=isset($_POST['file_doc'])?$_POST['file_doc']:'';
		$description=isset($_POST['description'])?$_POST['description']:'';
		$introtext=Common::getIntroText(trim(strip_tags($description)),200,'');
		$original=isset($_POST['original'])?$_POST['original']:'';
		$secure_code=isset($_POST['secure_code'])?$_POST['secure_code']:'';
		if($secure_code!='' && isset($_SESSION['secure_code']) && $_SESSION['secure_code']!=$secure_code)
		{
			$error.='Mã bảo mật không đúng!<br>';
		}

		$ordering=isset($_POST['ordering'])?$_POST['ordering']:0;
		$meta_title=isset($_POST['meta_title'])?$_POST['meta_title']:'';
		$meta_keyword=isset($_POST['meta_keyword'])?$_POST['meta_keyword']:'';
		$meta_description=isset($_POST['meta_description'])?$_POST['meta_description']:'';
		$create_date=time();
		$edit_date=$create_date;
		$publish_date=$create_date;
		$is_hot=0;
		if($error==''){
		$array_input=array('title'=>$title,'alias'=>Common::generate_slug($title),'cat_id'=>$cat_id,'file_doc'=>$file_doc,'description'=>$description,'introtext'=>$introtext,'orginal'=>$original,'is_hot'=>$is_hot,'meta_title'=>$meta_title,'meta_keyword'=>$meta_keyword,'meta_description'=>$meta_description,'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date,'user_id'=>$user_id,'username'=>$username,'status'=>0);
		$ok=CommonModel::insertObject($array_input,'e_doc');
		if($ok==0){
			$status=0;
		}else {
			$status=1;
			$error.='Upload thành công, cảm ơn bạn!';
			//Activty
			$users = $this->users;
			$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['id']));
			$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa upload tài liệu lên Tuyensinh247</p>';
			$activity_text = EActivity::genActivityText($users, $text);
			CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
		}
		}               
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	/*END*/
	/*ThangLQ*/
	public function actionRegister()
	{
		$username = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';
		$password = isset($_POST['password']) ? trim(strip_tags($_POST['password'])) : '';
		$re_password = isset($_POST['re_password']) ? trim(strip_tags($_POST['re_password'])) : '';
		$email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
		$fullname = isset($_POST['fullname']) ? trim(strip_tags($_POST['fullname'])) : '';
		$secure_code = isset($_POST['secure_code']) ? trim(strip_tags($_POST['secure_code'])) : '';
		$error='';
		$status=0;
		if($username=='')
		{
			$error.='Vui lòng nhập tên đăng nhập!<br>';
		}
		else
		{
			if((strlen($username)<6 && strlen($username)>24) || !preg_match('/[a-zA-Z0-9+]/si',$username))
			{
				$error.='Tên đăng nhập từ 6 - 24 ký tự, không gồm ký tự đặc biệt!<br>';
			}
			else
			{
				$check_user=EUser::getUserByUsername($username);
			}
		}
		if($password=='')
		{
			$error.='Vui lòng nhập mật khẩu đăng nhập!<br>';
		}
		if($re_password=='')
		{
			$error.='Vui lòng nhập lại mật khẩu!<br>';
		}
		if($password!=$re_password)
		{
			$error.='Mật khẩu không đúng!<br>';
		}
		if($email=='')
		{
			$error.='Vui lòng nhập địa chỉ email!<br>';
		}
		else if(!Common::validateEmailSyntax($email))
		{
			$error.='Email không hợp lệ!<br>';
		}
		if($fullname=='')
		{
			$error.='Vui lòng nhập họ tên đầy đủ!<br>';
		}
		if($secure_code=='')
		{
			$error.='Vui lòng nhập mã bảo mật!<br>';
		}
		else
		{
			if($secure_code!=$_SESSION['secure_code'])
			{
				$error.='Mã bảo mật không đúng!<br>';
			}
		}
		if($error!='')
		{
			$output=array('error'=>$error,'status'=>0);
		}
		else
		{
			/*Reset capcha*/
			Common::resetCapcha();
			$pass=Common::genPass($password);
			$array_input=array('username'=>mysql_escape_string($username),'password'=>mysql_escape_string($pass),'email'=>mysql_escape_string($email),'fullname'=>mysql_escape_string($fullname),'status'=>1,'create_date'=>time());
			$user_id=CommonModel::insertObject($array_input,'e_user');
			if($user_id!=0)
			{
				$status=1;
				$error='Đăng ký thành công';
				$_SESSION["userid"]=$user_id;
				$_SESSION["username"]=$username;
				//Tao 1 tai khoan tien
				$array_account=array('user_id'=>$user_id,'username'=>$username);
				CommonModel::insertObject($array_account,'e_account');
				//Tao 1 tai khoan User Point
				$array_user_point=array('user_id'=>$user_id,'username'=>$username,'point_1'=>10,'point_2'=>0,'level'=>1);
				CommonModel::insertObject($array_user_point,'e_user_point');
				//Tao 1 tai khoan User Point Qa
				$array_user_point_qa=array('user_id'=>$user_id,'username'=>$username,'point_1'=>0,'point_2'=>0);
				CommonModel::insertObject($array_user_point_qa,'e_user_point_qa');
				//Tao 1 tai khoan User Fan
				$array_user_fan = array('user_id'=>$user_id,'username'=>$username,'fan'=>0);
				CommonModel::insertObject($array_user_fan,'e_user_fan');
				//Activity
				$users = EUser::getUserById($user_id);
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['id']));
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa gia nhập thành viên Tuyensinh247.com</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
				//End activity
                $a=CommonModel::logDevice();
			}
			else
			{
				$status=0;
				$error='Có lỗi trong quá trình xử lý';
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
	}
	public function actionLogout()
	{
		if(isset($_SESSION['userid'])) unset($_SESSION['userid']);
		if(isset($_SESSION['username'])) unset($_SESSION['username']);
		exit();
	}
	public function actionLogin()
	{
		$username = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';
		$password = isset($_POST['password']) ? trim(strip_tags($_POST['password'])) : '';
		$error='';
		$status=0;
		if($username=='')
		{
			$error .= 'Vui lòng nhập tên đăng nhập!<br />';
		}
		
		if($password=='')
		{
			$error .= 'Vui lòng nhập mật khẩu đăng nhập!<br />';
		}
		if($error=='')
		{
			$pass=Common::genPass($password);
			$check=EUser::getUserLogin($username,$pass);
			if($check)
			{
				$_SESSION['userid']=$check['id'];
				$_SESSION['username']=$check['username'];
				$error.= "Đăng nhập thành công!<br>";
				$status=1;
                
                $a=CommonModel::logDevice();
                $user_info=EUser::getUserById($_SESSION['userid']);
                if($user_info['city_id']==0 || $user_info['district_id']==0 || $user_info['school_id']==0 || $user_info['level_number']==0 || $user_info['mobile']=='' ){
                    
                    $status=3;
                }
                if($check['teacher_id']!=0){
                    $status=2;
                }
			}
			else
			{
				$error.= "Tên đăng nhập hoặc Mật khẩu không chính xác. Vui lòng thử lại!<br>";
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	public function actionForgot()
	{
		$error='';
		$email=isset($_POST['email'])?$_POST['email']:'';
		$secure_code=isset($_POST['secure_code'])?$_POST['secure_code']:'';
		if($email=='')
		{
			$error .= 'Vui lòng nhập email của bạn!<br />';
		}
		else
		{
			$check=Common::validateEmailSyntax($email);
			if(!$check)
			{
				$error .= 'Địa chỉ email không hợp lệ!<br />';
			}
		}
		if($secure_code=='')
		{
			$error .= 'Vui lòng nhập mã bảo mật!<br />';
		}
		else
		{
			if(!isset($_SESSION['secure_code']) || (isset($_SESSION['secure_code']) && $_SESSION['secure_code']!=$secure_code))
			{
				$error .= 'Mã bảo mật không đúng!<br/>';
			}
		}
		if($error)
		{
			$output=array('error'=>$error,'status'=>0);
		}
		else
		{
			unset($_SESSION['secure_code']);
			$rs = EUser::getUserByEmail($email);
			if(!$rs)
			{
				$error .= "Địa chỉ email không tồn tại trong hệ thống!<br />";
				$status=0;
			}
			else 
			{
				$newpass = substr(md5(rand(5555,9999)),0,8);
				$pass=Common::genPass($newpass);
				$ok=CommonModel::updateObject(array('password'=>$pass),'id',$rs['id'],'e_user');
				if($ok>=0)
				{
					$error .= 'Xin vui lòng kiểm tra hòm thư để biết mật khẩu mới!';
					$status=1;
					/*Gui email*/
					//$template_mail= $this->renderPartial("application.views.tsEmailTemplate.forgot", array('username'=>$rs['username'],'password'=>$newpass,'full_name'=>$rs['full_name']),true);
					$template_mail='';
					Yii::import("application.vendors.PhpMailer",true);        
					$mail = new PHPMailer(true);
					$mail->IsSMTP();
					$mailer = new Mailer($mail);
											
					$mailer->setContent("Lấy lại mật khẩu trên Tuyensinh247.com",$template_mail);
					$mailer->sendMail2($email);
				}
			}
			$output=array('error'=>$error,'status'=>$status);
		}

		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
	}
	public function actionAddQaLesson()
	{		
		$user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$username=isset($_SESSION['username']) ? $_SESSION['username'] : '';
		$description=isset($_POST['description']) ? $_POST['description']:'';
		$lesson_id=isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
		$parent_id=isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
		$error='';
		$status=0;
		if(!isset($_SESSION['userid']))
		{
			$error.='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		if($description=='')
		{
			$error.='Vui lòng nhập nội dung câu hỏi, trả lời.<br>';
		}
		if($error=='')
		{
			$create_date=time();
			$edit_date=$create_date;
			$publish_date=$create_date;
			
			$array_input=array('lesson_id'=>$lesson_id,'parent_id'=>$parent_id,'description'=>mysql_escape_string($description),'user_id'=>$user_id,'username'=>mysql_escape_string($username),'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date);
			$ok = CommonModel::insertObject($array_input,'e_qa_lesson');
			if($ok>=0)
			{
				$status=1;
				$error='Gửi thành công! Cảm ơn bạn!';
			}
			else
			{
				$error='Có lỗi trong quá trình xử lý!';
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
	}
	public function actionLikeQaLesson()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
			echo $error;
			exit();
		}
		$user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
		$is_like='like_'.$qa_id.$user_id;
		if(!isset($_COOKIE[$is_like]))
		{
			$expire=time()+3600;
			setcookie($is_like,$is_like,$expire,'/');
			$ok=EQaLesson::updateQaLike($qa_id);
			if($ok>=0) echo 'Like thành công!';
		}
		else
		{
			echo 'Bạn đã Like câu này rồi!';
		}
		exit();
	}
	public function actionPostChat()
	{	
		$error='';
		$status=0;
		if(!isset($_SESSION['userid']))
		{
			$error.='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.';
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		$user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$username=isset($_SESSION['username']) ? $_SESSION['username'] : '';
		$is_mod=isset($this->users['is_mod']) ? intval($this->users['is_mod']) : 0;
		$avatar=isset($this->users['avatar']) ? $this->users['avatar'] : '';
		$description=isset($_POST['description']) ? $_POST['description']:'';
		$description=Common::bbcodeFormat($description);
		$lesson_id=isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;		
		
		$chat_id=0;
		if($description=='')
		{
			$error.='Vui lòng nhập nội dung bình luận.<br>';
		}
		if($error=='')
		{
			$create_date=time();
			
			$array_input=array('lesson_id'=>$lesson_id,'description'=>mysql_escape_string($description),'user_id'=>$user_id,'username'=>mysql_escape_string($username),'is_mod'=>$is_mod,'avatar'=>mysql_escape_string($avatar),'create_date'=>$create_date);
			$ok = CommonModel::insertObject($array_input,'e_chat');
			if($ok>=0)
			{
				$status=1;
				//Response
				if($avatar!='')
					$link_avatar=Common::getImage($avatar,'learning/avatar','');
				else
					$link_avatar=Yii::app()->params['static_url'].'/demo/avatar.png';
				$sub_class='cl333'; 
				if($is_mod==1) $sub_class='clred';
				$error='<li id="chat_'.$ok.'" class="clearfix"><a class="fl magr10" href="javascript:" rel="nofollow"><img src="'.$link_avatar.'"></a><div class="clearfix"><h3><a class="'.$sub_class.'" href="javascript:" rel="nofollow"><strong>'.$username.'</strong></a></h3><p class="cl666">'.$description.'</p></div></li>';
				$chat_id=$ok;
			}
			else
			{
				$error='Có lỗi trong quá trình xử lý!';
			}
		}
		$output=array('error'=>$error,'status'=>$status,'chat_id'=>$chat_id);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
	}
	
	public function actionVoteLesson()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$lesson_id=isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
        $vote=isset($_POST['vote']) ? intval($_POST['vote']) : 0;
		$comment=isset($_POST['comment']) ? trim($_POST['comment']) :'';
		$is_vote='vote_'.$lesson_id.$user_id;
		if(!isset($_COOKIE[$is_vote]))
		{
			$expire=time()+3600;
			setcookie($is_vote,$is_vote,$expire,'/');
			$ok=ELesson::voteLesson($lesson_id,$user_id,$vote,$comment);
			if($ok>=0) echo 'Gửi bình luận và Vote thành công!';
		}
		else
		{
			echo 'Bạn đã Vote rồi!';
		}
		exit();
	}
	//Nop hoc phi mon
	public function actionChargeCat()
	{
		$status=0;//Loi
		$error = '';
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 2;//Chua dang nhap
			echo $status;
			exit();
		}
		else
		{
			$users=$this->users;
			$cat_id=isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
			
			$cat_info = ECat::getCatById($cat_id);
			if(!$cat_info)
			{
				$error = 'Môn học không tồn tại trên hệ thống!';
				$status = 3;//Mon hoc khong dung
				echo $status;
				exit();
			}
			$price=isset($cat_info['price']) ? $cat_info['price']:0;
			if($users['money']<$price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;//Khong du tien
				echo $status;
				exit();
			}
			$user_id=$users['id'];
			$username=$users['username'];
			$type=1;//Loai giao dich
			$ip=Common::getRealIpAddr();
			$arr_type_transaction=LoadConfig::$arr_type_transaction;
			$introtext = isset($arr_type_transaction[$type]) ? $arr_type_transaction[$type]:'';
			$create_h=date('H');
			$create_day=date('d');
			$create_month=date('m');
			$create_year=date('Y');
			$create_date=date('Y-m-d');
			$create_time=time();
			$expired_date = $create_time+86400*365;
			
			$array_input=array('content_type'=>1,'cat_id'=>$cat_id,'money'=>$price,'money_telco'=>$price,'user_id'=>$user_id,'username'=>$username,'type'=>$type,'ip'=>$ip,'introtext'=>mysql_escape_string($introtext),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time,'expired_date'=>$expired_date,'status'=>1);
			$charge=EAccount::updateMoney($user_id,$price);
			if($charge)
			{
				$total = ELesson::getTotalLessonByCat($cat_id);//Tong so bai giang thuoc mon
				$array_input['total'] = $total;
				//Tim giao vien day mon nay
				$arr_subjects_lesson_match = LoadConfig::$arr_subjects_lesson_match;
				$subject_id = 0;//Mon ma giao vien day
				foreach($arr_subjects_lesson_match as $subject_id=>$cat_id_match)
				{
					if($cat_id_match == $cat_id)
					{
						break;
					}
				}
				$teachers = ETeacher::getTeacher();
				$list_teacher_id = '';
				if($teachers)
				foreach($teachers as $value)
				{
					$subject=$value['subject'];
					$subject=explode(',',$subject);
					if(in_array($subject_id,$subject))
					{
						$teacher_id = $value['id'];
						$array_input['teacher_id'] = $teacher_id;
						$array_input['percent'] = $value['percent'];
						$list_teacher_id .=$teacher_id.',';
						$total_lesson = ELesson::getTotalLessonByCatTeacher($cat_id,$teacher_id);//Tong so bai giang cua thay trong mon $cat_id
						$array_input['total_lesson'] = $total_lesson;
						$ok=CommonModel::insertObject($array_input,'e_transaction');
					}
				}
				$list_teacher_id = rtrim($list_teacher_id,',');
				if($ok>=0)
				{
					//Insert e_lesson_log
					$city_id = $users['city_id'];
					$city_name = $users['city_name'];
					$district_id = $users['district_id'];
					$district_name = $users['district_name'];
					
					$array_lesson_log=array('user_id'=>$user_id,'username'=>$username,'cat_id'=>$cat_id,'cat_title'=>$cat_info['title'],'teacher_id'=>mysql_escape_string($list_teacher_id),'city_id'=>$city_id,'district_id'=>$district_id,'city_name'=>mysql_escape_string($city_name),'district_name'=>mysql_escape_string($district_name),'price'=>$price,'ip'=>$ip,'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
					$result = CommonModel::insertObject($array_lesson_log,'e_lesson_log');
					$error = 'Nộp học phí thành công!';
					$status=1;//Thanh cong
				}
				else
				{
					$error = 'Có lỗi trong quá trình xử lý!';
				}
			}
			else
			{
				echo 'Có lỗi trong quá trình xử lý!';
			}
		}
		echo $status;
		exit();
	}
	//Nop hoc phi theo khoa hoc
	public function actionChargeCourse()
	{
		$error = '';
		$status = 0;
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 2;
			echo $status;
			exit();
		}
		else
		{
			$users=$this->users;
			$course_id=isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
			$course_info = ECourse::getCourseById($course_id);
			if(!$course_info)
			{
				$error = 'Không tồn tại khóa học này trên hệ thống!';
				$status = 3;
				echo $status;
				exit();
			}
			$cat_id = isset($course_info['cat_id']) ? intval($course_info['cat_id']) : 0;
			$price=isset($course_info['price']) ? $course_info['price']:0;
			if($users['money'] + $users['money_sub']<$price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status =4;
				echo $status;
				exit();
			}
			$user_id=$users['id'];
			$username=$users['username'];
			$type=2;//Loai giao dich
			$ip=Common::getRealIpAddr();
			$arr_type_transaction=LoadConfig::$arr_type_transaction;
			$introtext = isset($arr_type_transaction[$type]) ? $arr_type_transaction[$type]:'';
			$create_h=date('H');
			$create_day=date('d');
			$create_month=date('m');
			$create_year=date('Y');
			$create_date=date('Y-m-d');
			$create_time=time();
			$expired_date = $create_time+86400*365;
			
			$array_input=array('content_type'=>1,'cat_id'=>$cat_id,'course_id'=>$course_id,'money'=>$price,'money_telco'=>$price,'user_id'=>$user_id,'username'=>$username,'type'=>$type,'ip'=>$ip,'introtext'=>mysql_escape_string($introtext),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time,'expired_date'=>$expired_date,'status'=>1);

			list($charge, $money_real, $money_real_sub)=EAccount::updateMoney($user_id,$price);
			if($charge)
			{
				$total = ELesson::getTotalLessonByCourse($course_id);//Tong so bai giang thuoc mon
				$array_input['total'] = $total;
				//Tim giao vien day mon nay
				$arr_subjects_lesson_match = LoadConfig::$arr_subjects_lesson_match;
				$subject_id = 0;//Mon ma giao vien day
				foreach($arr_subjects_lesson_match as $subject_id=>$cat_id_match)
				{
					if($cat_id_match == $cat_id)
					{
						break;
					}
				}
				$teachers = ETeacher::getTeacher();
				$list_teacher_id = '';
				if($teachers)
				foreach($teachers as $value)
				{
					$subject=$value['subject'];
					$subject=explode(',',$subject);
					if(in_array($subject_id,$subject))
					{
						$teacher_id = $value['id'];
						$array_input['teacher_id'] = $teacher_id;
						$array_input['percent'] = $value['percent'];
						$list_teacher_id .=$teacher_id.',';
						$total_lesson = ELesson::getTotalLessonByCourseTeacher($course_id,$teacher_id);//Tong so bai giang cua thay trong mon $cat_id
						$array_input['total_lesson'] = $total_lesson;
						//$ok=CommonModel::insertObject($array_input,'e_transaction');
						if($money_real_sub==0)
						{
							$ok=CommonModel::insertObject($array_input,'e_transaction');
						}
						else
						{
							//Log tai khoan khuyen mai
							if($money_real_sub!=0)
							{
								$array_input['status']=0;
								$array_input['money']=$money_real_sub;
								$array_input['money_telco']=$money_real_sub;
								$ok = CommonModel::insertObject($array_input,'e_transaction');
							}
							//Log tai khoan chinh
							if($money_real!=0)
							{
								$array_input['status']=1;
								$array_input['money']=$money_real;
								$array_input['money_telco']=$money_real;
								$ok = CommonModel::insertObject($array_input,'e_transaction');
							}
                            
						}
					}
				}
				$list_teacher_id = rtrim($list_teacher_id,',');
				
				if($ok>=0)
				{
					$error = 'Nộp học phí thành công!';
					//Insert e_lesson_log
					$city_id = $users['city_id'];
					$city_name = $users['city_name'];
					$district_id = $users['district_id'];
					$district_name = $users['district_name'];
					//Thong tin cat
					$cat_info = ECat::getCatById($cat_id);
					$cat_title = isset($cat_info['title']) ? $cat_info['title']:'';
					$course_title = isset($course_info['title']) ? $course_info['title']:'';
					
					$array_lesson_log=array('user_id'=>$user_id,'username'=>$username,'cat_id'=>$cat_id,'cat_title'=>$cat_title,'course_id'=>$course_id,'course_title'=>mysql_escape_string($course_title),'teacher_id'=>mysql_escape_string($list_teacher_id),'city_id'=>$city_id,'district_id'=>$district_id,'city_name'=>mysql_escape_string($city_name),'district_name'=>mysql_escape_string($district_name),'price'=>$price,'ip'=>$ip,'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
					
					CommonModel::insertObject($array_lesson_log,'e_lesson_log');
					$status = 1;
				}
				else $error = 'Có lỗi trong quá trình xử lý!';
			}
			else
			{
				$error = 'Có lỗi trong quá trình xử lý!';
			}
		}
		echo $status;
		exit();
	}
	public function actionCheckChargeCourse()
	{
		$status=0;
		$error='';
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';			
			$status = 2;
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		else
		{
			$users=$this->users;
			$course_id=isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
			$course_info = ECourse::getCourseById($course_id);
			if(!$course_info)
			{
				$error = 'Không tồn tại khóa học này trên hệ thống!';
				$status = 3;
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			
			$cat_id = isset($course_info['cat_id']) ? intval($course_info['cat_id']) : 0;
			$price=isset($course_info['price']) ? $course_info['price']:0;
			if($users['money'] + $users['money_sub']<$price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;//Khong du tien
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			else
			{
				$status=1;
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	
	//Chuyen de
	public function actionCheckChargeTopic()
	{
		$status=0;
		$error='';
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';			
			$status = 2;
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		else
		{
			$users=$this->users;
			$topic_id=isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
			$topic_info = ETopic::getTopicById($topic_id);
			if(!$topic_info)
			{
				$error = 'Không tồn tại chuyên đề này trên hệ thống!';
				$status = 3;
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			$price=isset($topic_info['price']) ? $topic_info['price']:0;
			if($users['money'] + $users['money_sub']<$price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;//Khong du tien
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			else
			{
				$status=1;
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	public function actionChargeTopic()
	{
		$error = '';
		$status = 1;
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 2;
			echo $status;
			exit();
		}
		else
		{
			$users=$this->users;
			$topic_id=isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
			
			$topic_info = ETopic::getTopicById($topic_id);
			if(!$topic_info)
			{
				$error = 'Không tồn tại chuyên đề này trên hệ thống!';
				$status = 3;
				echo $status;
				exit();
			}
			$cat_id = isset($topic_info['cat_id']) ? intval($topic_info['cat_id']) : 0;
			$course_id = isset($topic_info['course_id']) ? intval($topic_info['course_id']) : 0;
			$price=isset($topic_info['price']) ? $topic_info['price']:0;
			if($users['money']+$users['money_sub']<$price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;
				echo $status;
				exit();
			}
			$user_id=$users['id'];
			$username=$users['username'];
			$type=3;//Loai giao dich
			$ip=Common::getRealIpAddr();
			$arr_type_transaction=LoadConfig::$arr_type_transaction;
			$introtext = isset($arr_type_transaction[$type]) ? $arr_type_transaction[$type]:'';
			$create_h=date('H');
			$create_day=date('d');
			$create_month=date('m');
			$create_year=date('Y');
			$create_date=date('Y-m-d');
			$create_time=time();
			$expired_date = $create_time+86400*365;
			
			$array_input=array('content_type'=>1,'cat_id'=>$cat_id,'course_id'=>$course_id,'topic_id'=>$topic_id,'money'=>$price,'money_telco'=>$price,'user_id'=>$user_id,'username'=>$username,'type'=>$type,'ip'=>$ip,'introtext'=>mysql_escape_string($introtext),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time,'expired_date'=>$expired_date,'status'=>1);
			
			list($charge, $money_real, $money_real_sub)=EAccount::updateMoney($user_id,$price);
			if($charge)
			{
				$total = ETopic::getTotalLessonByTopic($topic_id); // Tong so bai giang cua chuyen de
				$array_input['total'] = $total;
				$teachers = ETopicTeacher::getTeacherByTopic($topic_id);
				$list_teacher_id = '';
				$ok = 0;
				if(!empty($teachers))
				foreach($teachers as $value)
				{
					$teacher_id = $value['id'];
					$array_input['teacher_id'] = $teacher_id;
					$array_input['percent'] = $value['percent'];
					$list_teacher_id .=$teacher_id.',';
					$total_lesson = ETopic::getTotalLessonByTopicTeacher($topic_id,$teacher_id);//Tong so bai giang cua thay
					$array_input['total_lesson'] = $total_lesson;
					if($money_real_sub==0)
					{
						$ok=CommonModel::insertObject($array_input,'e_transaction');
					}
					else
					{
						//Log tai khoan khuyen mai
						if($money_real_sub!=0)
						{
							$array_input['status']=0;
							$array_input['money']=$money_real_sub;
							$array_input['money_telco']=$money_real_sub;
							$ok=CommonModel::insertObject($array_input,'e_transaction');
						}
						//Log tai khoan chinh
						if($money_real!=0)
						{
							$array_input['status']=1;
							$array_input['money']=$money_real;
							$array_input['money_telco']=$money_real;
							$ok=CommonModel::insertObject($array_input,'e_transaction');
						}
					}
				}
				$list_teacher_id = rtrim($list_teacher_id,',');
				if($ok>=0)
				{
					//Insert e_lesson_log
					$city_id = $users['city_id'];
					$city_name = $users['city_name'];
					$district_id = $users['district_id'];
					$district_name = $users['district_name'];
					//Thong tin mon hoc
					$cat_info = ECat::getCatById($cat_id);
					$cat_title = isset($cat_info['title']) ? $cat_info['title']:'';
					//Thong tin khoa hoc
					$course_info = ECourse::getCourseById($course_id);
					$course_title = isset($course_info['title']) ? $course_info['title']:'';
					$topic_title = isset($topic_info['title']) ? $topic_info['title']:'';
					
					$array_lesson_log=array('user_id'=>$user_id,'username'=>$username,'cat_id'=>$cat_id,'cat_title'=>$cat_title,'course_id'=>$course_id,'course_title'=>mysql_escape_string($course_title),'topic_id'=>$topic_id,'topic_title'=>mysql_escape_string($topic_title),'teacher_id'=>mysql_escape_string($list_teacher_id),'city_id'=>$city_id,'district_id'=>$district_id,'city_name'=>mysql_escape_string($city_name),'district_name'=>mysql_escape_string($district_name),'price'=>$price,'ip'=>$ip,'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
					
					CommonModel::insertObject($array_lesson_log,'e_lesson_log');
					//Cap nhat luot mua chuyen de
					ETopic::updateHitTopic($topic_id);
					
					$error = 'Nộp học phí thành công!';
					$status = 1;
				}
				else echo 'Có lỗi trong quá trình xử lý!';
			}
			else
			{
				echo 'Có lỗi trong quá trình xử lý!';
			}
		}
		echo $status;
		exit();
	}
	
	//Bai giang
	public function actionCheckChargeLesson()
	{
		$status=0;
		$error='';
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';			
			$status = 2;
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		else
		{
			$users=$this->users;
			$lesson_id=isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
			$lesson_info = ELesson::getLessonById($lesson_id);
			if(!$lesson_info)
			{
				$error = 'Không tồn tại bài giảng này trên hệ thống!';
				$status = 3;
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			$topic_id = $lesson_info['topic_id'];
			$topic_info = ETopic::getTopicById($topic_id);
			$price=isset($topic_info['price']) ? $topic_info['price']:0;
			if($users['money'] + $users['money_sub'] < $price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;//Khong du tien
				$output=array('error'=>$error,'status'=>$status);
				$output=json_encode($output);
				header("Content-Type: application/json; charset=utf-8");
				echo $output;
				exit();
			}
			else
			{
				$status=1;
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	public function actionChargeLesson()
	{
		$status = 0;
		$error = '';
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 2;
			echo $status;
			exit();
		}
		else
		{
			$users=$this->users;
			$lesson_id=isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
			$lesson_info = ELesson::getLessonById($lesson_id);
			if(!$lesson_info)
			{
				$status = 3;
				$error = 'Bài giảng không tồn tại trên hệ thống';
				echo $status;
				exit();
			}
			$cat_id = isset($lesson_info['cat_id']) ? intval($lesson_info['cat_id']) : 0;
			$course_id = isset($lesson_info['course_id']) ? intval($lesson_info['course_id']) : 0;
			$topic_id = isset($lesson_info['topic_id']) ? intval($lesson_info['topic_id']) : 0;
			$topic_info=ETopic::getTopicById($topic_id);
			//$price=isset($lesson_info['price']) ? $lesson_info['price']:0;			
			$price=isset($topic_info['price']) ? $topic_info['price'] : 0;
			
			if($users['money'] + $users['money_sub'] < $price)
			{
				$error = 'Bạn không đủ tiền. Vui lòng nạp thêm tiền để có thể đăng ký học ngay!';
				$status = 4;
				echo $status;
				exit();
			}
			
			$user_id=$users['id'];
			$username=$users['username'];
			$type=4;//Mua bai giang
			$ip=Common::getRealIpAddr();
			$arr_type_transaction=LoadConfig::$arr_type_transaction;
			$introtext = isset($arr_type_transaction[$type]) ? $arr_type_transaction[$type]:'';
			$create_h=date('H');
			$create_day=date('d');
			$create_month=date('m');
			$create_year=date('Y');
			$create_date=date('Y-m-d');
			$create_time=time();
			$expired_date = $create_time+86400*365;
			
			$array_input=array('cat_id'=>$cat_id,'course_id'=>$course_id,'topic_id'=>$topic_id,'content_id'=>0,'content_type'=>1,'money'=>$price,'money_telco'=>$price,'user_id'=>$user_id,'username'=>$username,'type'=>$type,'ip'=>$ip,'introtext'=>mysql_escape_string($introtext),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time,'expired_date'=>$expired_date,'status'=>1);
			
			list($charge, $money_real, $money_real_sub)=EAccount::updateMoney($user_id,$price);
			if($charge>=0)
			{
				/*
				$total = ETopic::getTotalLessonByTopic($topic_id); // Tong so bai giang cua chuyen de
				$array_input['total'] = $total;
				$teacher_id = $lesson_info['teacher_id'];
				$teacher_info = ETeacher::getTeacherById($teacher_id);
				$array_input['teacher_id'] = $teacher_id;
				$array_input['percent'] = $teacher_info['percent'];
				$list_teacher_id = $teacher_id;
				
				$total_lesson = ETopic::getTotalLessonByTopicTeacher($topic_id,$teacher_id);//Tong so bai giang cua thay
				$array_input['total_lesson'] = $total_lesson;
				$ok=CommonModel::insertObject($array_input,'e_transaction');
				*/
				
				$total = ETopic::getTotalLessonByTopic($topic_id); // Tong so bai giang cua chuyen de
				$array_input['total'] = $total;
				$teachers = ETopicTeacher::getTeacherByTopic($topic_id);
				$list_teacher_id = '';
				$ok = 0;
				if(!empty($teachers))
				foreach($teachers as $value)
				{
					$teacher_id = $value['id'];
					$array_input['teacher_id'] = $teacher_id;
					$array_input['percent'] = $value['percent'];
					$list_teacher_id .=$teacher_id.',';
					$total_lesson = ETopic::getTotalLessonByTopicTeacher($topic_id,$teacher_id);//Tong so bai giang cua thay
					$array_input['total_lesson'] = $total_lesson;
					if($money_real_sub==0)
					{
						$ok=CommonModel::insertObject($array_input,'e_transaction');
					}
					else
					{
						//Log tai khoan khuyen mai
						if($money_real_sub!=0)
						{
							$array_input['status']=0;
							$array_input['money']=$money_real_sub;
							$array_input['money_telco']=$money_real_sub;
							$ok=CommonModel::insertObject($array_input,'e_transaction');
						}
						//Log tai khoan chinh
						if($money_real!=0)
						{
							$array_input['status']=1;
							$array_input['money']=$money_real;
							$array_input['money_telco']=$money_real;
							$ok=CommonModel::insertObject($array_input,'e_transaction');
						}
					}
				}
				$list_teacher_id = rtrim($list_teacher_id,',');
				
				if($ok>=0)
				{
					//Insert e_lesson_log
					$city_id = $users['city_id'];
					$city_name = $users['city_name'];
					$district_id = $users['district_id'];
					$district_name = $users['district_name'];
					//Thong tin mon hoc
					$cat_info = ECat::getCatById($cat_id);
					$cat_title = isset($cat_info['title']) ? $cat_info['title']:'';
					//Thong tin khoa hoc
					$course_info = ECourse::getCourseById($course_id);
					$course_title = isset($course_info['title']) ? $course_info['title']:'';
					$topic_title = isset($topic_info['title']) ? $topic_info['title']:'';
					$lesson_title = isset($lesson_info['title']) ? $lesson_info['title']:'';
					
					$array_lesson_log=array('user_id'=>$user_id,'username'=>$username,'cat_id'=>$cat_id,'cat_title'=>$cat_title,'course_id'=>$course_id,'course_title'=>mysql_escape_string($course_title),'topic_id'=>$topic_id,'topic_title'=>mysql_escape_string($topic_title),'lesson_id'=>0,'lesson_title'=>'','teacher_id'=>mysql_escape_string($list_teacher_id),'city_id'=>$city_id,'district_id'=>$district_id,'city_name'=>mysql_escape_string($city_name),'district_name'=>mysql_escape_string($district_name),'price'=>$price,'ip'=>$ip,'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
					
					CommonModel::insertObject($array_lesson_log,'e_lesson_log');
					//Cap nhat luot hoc
					ELesson::updateHitLesson($lesson_id);
					//ETopic::updateHitTopic($topic_id);
					$error = 'Nộp học phí thành công!';
					$status = 1;
				}
				else $error = 'Có lỗi trong quá trình xử lý!';
			}
			else
			{
				$error = 'Có lỗi trong quá trình xử lý!';
			}
		}
		echo $status;
		exit();
	}
	public function actionPostAnswerMember()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$exam_id=isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;
		$question_id=isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
		$answer_member=isset($_POST['answer_member']) ? $_POST['answer_member'] : '';
		$file_answer=isset($_POST['file_answer']) ? $_POST['file_answer'] : '';
		$type=isset($_POST['type']) ? intval($_POST['type']) : 0;
        
		$users=$this->users;
		$username=$users['username'];
		$user_id=$users['id'];		
		$create_date=time();
		$edit_date=$create_date;
		$publish_date=$create_date;
		
		$array_input=array('question_id'=>$question_id,'user_id'=>$user_id,'username'=>$username,'answer_member'=>mysql_escape_string($answer_member),'file_answer'=>mysql_escape_string($file_answer),'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date,'status'=>0,'type'=>$type);
		$is_answer='answer_'.$question_id.$user_id;
        
		if(isset($_COOKIE[$is_answer]))
		{
			echo 'Cám ơn bạn đã đóng góp lời giải!';
			exit();
		}
		$ok = 0;
		if($answer_member=='' && $file_answer=='')
		{
			echo 'Vui lòng nhập nội dung lời giải!';
			exit();
		}
		else
		{
			$ok=CommonModel::insertObject($array_input,'e_question_answer');
		}
		if($ok>=0)
		{
			if(!isset($_COOKIE[$is_answer]))
			{
				$expire=time()+86400*365;
				setcookie($is_answer,$is_answer,$expire,'/');
			}
			
			echo 'Cảm ơn bạn. Chúng tôi sẽ duyệt lời giải của bạn sớm nhất có thể!';
			
			//Activity
			$detail = EQuestion::getQuestionById($question_id);
			if($detail)
			{
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['id']));
				$link_question = Url::createUrl('eQuestion/index',array('question_id'=>$question_id));
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa viết lời giải cho bài tập “<a target="_blank" class="clblue" href="'.$link_question.'">'.Common::getIntroText($detail['description'],50,'...').'</a>”</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
			}
		}
		else
		{
			echo 'Có lỗi trong quá trình xử lý';
		}
		exit();
	}
	
	public function actionPostCommentQuestion()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$question_id=isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
		$description=isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : '';
		
		$users=$this->users;
		$username=$users['username'];
		$user_id=$users['id'];		
		$create_date=time();
		$edit_date=$create_date;
		
		$ip_address = Common::getRealIpAddr();
		$array_input=array('question_id'=>$question_id,'user_id'=>$user_id,'username'=>$username,'description'=>mysql_escape_string($description),'create_date'=>$create_date,'edit_date'=>$edit_date,'ip_address'=>$ip_address,'status'=>0);
		
		$ok=CommonModel::insertObject($array_input,'e_question_comment');
		if($ok>=0)
		{
			echo 'Cảm ơn bạn. Chúng tôi sẽ duyệt bình luận của bạn sớm nhất có thể!';
			
			//Activity
			$detail = EQuestion::getQuestionById($question_id);
			if($detail)
			{
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['username']));
				$link_question = Url::createUrl('eQuestion/index',array('question_id'=>$question_id));
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa gửi bình luận cho bài tập “<a target="_blank" class="clblue" href="'.$link_question.'">'.Common::getIntroText($detail['description'],50,'...').'</a>”</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
			}
		}
		else
		{
			echo 'Có lỗi trong quá trình xử lý';
		}
		exit();
	}
	public function actionVoteAnswerVip()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$answer_id=isset($_POST['answer_id']) ? intval($_POST['answer_id']) : 0;
        $vote=isset($_POST['vote']) ? intval($_POST['vote']) : 0;
		$comment=isset($_POST['comment']) ? trim($_POST['comment']) :'';
        $question_id=isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
		$users=$this->users;
		$user_id = $users['id'];	
        $username=$users['username'];	
		$create_date=time();
        $edit_date=$create_date;
		$array_input=array('answer_id'=>$answer_id,'user_id'=>$user_id,'vote'=>$vote,'create_date'=>$create_date);
        
        $ip_address = Common::getRealIpAddr();
        $array_input_comment=array('question_id'=>$question_id,'user_id'=>$user_id,'username'=>$username,'description'=>mysql_escape_string($comment),'create_date'=>$create_date,'edit_date'=>$edit_date,'ip_address'=>$ip_address,'status'=>0);
		
		$is_vote_answer_vip='answer_'.$answer_id.$user_id;
		if(isset($_COOKIE[$is_vote_answer_vip]))
		{
			echo 'Bạn đã đánh giá lời giải VIP này rồi!';
			exit();
		}
		
		$ok=CommonModel::insertObject($array_input,'e_question_answer_vote');
        $ok2=CommonModel::insertObject($array_input_comment,'e_question_comment');
		if($ok>=0 && $ok2>0)
		{
			echo 'Cảm ơn bạn có đánh giá với lời giải VIP!';
			if(!isset($_COOKIE[$is_vote_answer_vip]))
			{
				$expire=time()+86400*365;
				setcookie($is_vote_answer_vip,$is_vote_answer_vip,$expire,'/');
			}
			//Activty
			$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['id']));
			$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> vừa gửi đánh giá cho lời giải VIP</p>';
			$activity_text = EActivity::genActivityText($users, $text);
			CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
		}
		else
		{
			echo 'Có lỗi trong quá trình xử lý';
		}
	}
	
	public function actionUpdateInfoUser()
	{
        $error='';
        $status=0;
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$user_id = isset($_SESSION['userid']) ? intval($_SESSION['userid']):0;
		$users = $this->users;
		$city_id=isset($_POST['city_id']) && $_POST['city_id']!=0 ? intval($_POST['city_id']) : $users['city_id'];
		$city_name=isset($_POST['city_name']) && $_POST['city_name']!='' ? $_POST['city_name'] : $users['city_name'];
		$district_id=isset($_POST['district_id']) && $_POST['district_id']!=0 ? intval($_POST['district_id']) : $users['district_id'];
		$district_name=isset($_POST['district_name']) && $_POST['district_name']!='' ? $_POST['district_name'] : $users['district_name'];
		$school_id=isset($_POST['school_id']) && $_POST['school_id']!=0 ? intval($_POST['school_id']) : $users['school_id'];
		$school_name=isset($_POST['school_name']) && $_POST['school_name']!='' ? $_POST['school_name'] : $users['school_name'];
		$mobile = isset($_POST['mobile']) && $_POST['mobile']!='' ? $_POST['mobile'] : $users['mobile'];
        $level_number = isset($_POST['level_number']) ? $_POST['level_number'] : '';
		$orginal_school = isset($_POST['orginal_school']) ? $_POST['orginal_school'] : '';
        
        if($city_id==0){
            $error= "Bạn vui lòng chọn Tỉnh/ Thành phố !";
        }else 
        if($district_id==0){
            $error= "Bạn vui lòng chọn Quận/ Huyện !";
        }else 
        if($school_id==0 && $orginal_school==''){
            $error= "Bạn vui lòng chọn hoặc nhập Trường nơi bạn theo học !";
        }else 
        if($level_number==0){
            $error= "Bạn vui lòng chọn Lớp bạn đang học !";
        }else 
        if($mobile==''){
            $error= "Bạn vui lòng nhập Số điện thoại của bạn hoặc người thân !";
        }else {        
		$array_input=array('city_id'=>$city_id,'city_name'=>mysql_escape_string($city_name),'district_id'=>$district_id,'district_name'=>mysql_escape_string($district_name),'school_id'=>$school_id,'school_name'=>mysql_escape_string($school_name),'mobile'=>mysql_escape_string($mobile),'level_number'=>mysql_escape_string($level_number),'orginal_school'=>mysql_escape_string($orginal_school));
		
		$status=CommonModel::updateObject($array_input,'id',$user_id,'e_user');  
        }     

        $output=array('error'=>$error,'status'=>$status);
        $output=json_encode($output);
        header("Content-Type: application/json; charset=utf-8");
        echo $output;
	}
	/*End*/
	//Lam bai tap le
	public function actionSubmitTest()
	{
		$error = '';
		$status = 0;
		if(!isset($_SESSION['userid']))
		{
			$error = 'Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 2;//Chua login
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		$user_id = isset($_SESSION['userid']) ? intval($_SESSION['userid']):0;
		$users = $this->users;
		$username = $users['username'];
		$avatar = $users['avatar'];
		$fbid = $users['fbid'];
		$answer_user = isset($_POST['answer_user']) ? intval($_POST['answer_user']) : 0;//Dap an thanh vien
		$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
		$detail = EQuestion::getQuestionById($question_id);
		$arr_answer = LoadConfig::$arr_answer;//Gia tri cua dap an
		if(!$detail)
		{
			$error = 'Bài tập không tồn tại trên hệ thống Tuyensinh247.com';
			$status = 3;//Không tồn tại
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		if($answer_user==0)
		{
			$error = 'Vui lòng chọn đáp án';
			$status = 4;//Chua chon dap an
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
		else
		{
			$arr_level_yes   = LoadConfig::$arr_level_yes;
            $arr_level_no    = LoadConfig::$arr_level_no;
			
			$answer = $detail['answer_yes_no'];//Dap an dung cu bai tap
			$topic_id = $detail['topic_id'];
			$level_id = $detail['level_id'];
			$cat_id = $detail['cat_id'];
			
			$yes_no = $answer==$answer_user ?  1 : 0;
			$point = 0;
			if($yes_no==1)
			{
				$point = isset($arr_level_yes[$detail["level_id"]]) ? $arr_level_yes[$detail["level_id"]]:0;
			}
			else
			{
				$point = isset($arr_level_no[$detail["level_id"]]) ? -$arr_level_no[$detail["level_id"]]:0;
			}
			$school_id = $users['school_id'];
			$school_name = $users['school_name'];
			$city_id = $users['city_id'];
			$city_name = $users['city_name'];
			$district_id = $users['district_id'];
			$district_name = $users['district_name'];
			$create_h = date('H');
			$create_day = date('d');
			$create_month = date('m');
			$create_year = date('Y');
			$create_date = date('Y-m-d');
			$create_time = time();
			$ip = Common::getRealIpAddr();
			$array_input = array('question_id'=>$question_id,'parent_id'=>$detail['parent_id'],'topic_id'=>$topic_id,'level_id'=>$level_id,'cat_id'=>$cat_id,'answer'=>$answer,'answer_user'=>$answer_user,'yes_no'=>$yes_no,'point'=>$point,'ip'=>$ip,'user_id'=>$user_id,'username'=>$username,'avatar'=>mysql_escape_string($avatar),'fbid'=>$fbid,'school_id'=>$school_id,'school_name'=>mysql_escape_string($school_name),'city_id'=>$city_id,'city_name'=>mysql_escape_string($city_name),'district_id'=>$district_id,'district_name'=>mysql_escape_string($district_name),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
			$ok = CommonModel::insertObject($array_input,'e_exam_log_2');// Log thi tung bai tap le
			if($detail['parent_id']!=0)
			{
				$array_input_2 = array('question_id'=>$detail['parent_id'],'parent_id'=>0,'topic_id'=>$topic_id,'level_id'=>$level_id,'cat_id'=>$cat_id,'answer'=>$answer,'answer_user'=>$answer_user,'yes_no'=>$yes_no,'point'=>$point,'ip'=>$ip,'user_id'=>$user_id,'username'=>$username,'avatar'=>mysql_escape_string($avatar),'fbid'=>$fbid,'school_id'=>$school_id,'school_name'=>mysql_escape_string($school_name),'city_id'=>$city_id,'city_name'=>mysql_escape_string($city_name),'district_id'=>$district_id,'district_name'=>mysql_escape_string($district_name),'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
				CommonModel::insertObject($array_input_2,'e_exam_log_2');// Log thi tung bai tap le cua bai tap ket hop
			}
			//Log du lieu tong hop: e_exam_log_result
			$total_yes = 0;
			$total_no = 0;
			$total = 1;
			if($yes_no==1) $total_yes=1;
			else $total_no=1;
			$array_input_3 = array('cat_id'=>$cat_id,'total_yes'=>$total_yes,'total_no'=>$total_no,'total'=>$total,'point'=>$point,'user_id'=>$user_id,'username'=>$username,'avatar'=>mysql_escape_string($avatar),'fbid'=>$fbid,'school_id'=>$school_id,'school_name'=>mysql_escape_string($school_name),'city_id'=>$city_id,'city_name'=>mysql_escape_string($city_name),'district_id'=>$district_id,'district_name'=>mysql_escape_string($district_name),'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>time());
			CommonModel::insertObject($array_input_3,'e_exam_log_result');
			//End
			if($ok>0)
			{
				$status = 1;
				$error = 'Nộp bài thành công';
				//Cap nhat diem thanh tich, level
				$user_point = EUserPoint::getUserPointByUser($user_id);
                $point_1 = isset($user_point["point_1"])? $user_point["point_1"]:0;
                $point_1 = $point_1 + $point;
                $level = Common::getLevelUserPoint($point_1);
                CommonModel::updateObjectNew(array("point_1"=>$point_1,'level'=>$level),array('user_id'=>$user_id),'e_user_point');
				//Cap nhat luot thi
				EQuestion::updateHit($question_id);
				//Activity
				$link_user_public = Url::createUrl('eUser/infoUserPublic',array('user_id'=>$users['id'],'alias'=>$users['username']));
				$link_question = Url::createUrl('eQuestion/index',array('question_id'=>$question_id));
				if($point > 0) $sub_text = 'được cộng';
				else $sub_text = 'vừa bị trừ';
				$text = '<p class="cl333"><a href="'.$link_user_public.'" target="_blank"><strong>'.$users['username'].' </strong></a> '.$sub_text.' <a target="_blank" class="clblue" href="'.$link_user_public.'">'.$point.'</a> ĐTT hoàn thành bài thi “<a target="_blank" class="clblue" href="'.$link_question.'">'.Common::getIntroText(trim(strip_tags($detail['description'])),50,'...').'</a>”</p>';
				$activity_text = EActivity::genActivityText($users, $text);
				CommonModel::insertObject(array('text'=>mysql_escape_string($activity_text),'create_date'=>time()),'e_activity');
			}
			else
			{
				$error = 'Có lỗi trong quá trình xử lý!';
			}
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	public function actionChangeLessonCat()
	{
		$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
		$lessons = ELesson::getLessonHome($cat_id,4);
		$lessons_free = ELesson::getLessonHomeFree($cat_id,4);
		$teachers = ETeacher::getTeacher();
		$this->renderPartial('application.views.eHome._lesson',
					  array('lessons'=>$lessons,'lessons_free'=>$lessons_free,'teachers'=>$teachers
		));
		exit();
	}
	public function actionRealtimeOnline()
	{
		$user_id = isset($_SESSION['userid']) ? intval($_SESSION['userid']):0;
		$users = $this->users;
		$exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;
		$result_1 = '';
		$result_2 = '';
		$check = 0;
		if($exam_id!=0)
		{
			$detail = EExam::getExamById($exam_id);
			$time_begin = isset($detail['time_begin']) ? $detail['time_begin'] : 0;
			$time_end = isset($detail['time_end']) ? $detail['time_end'] : 0;
			list($result_online_realtime,$result_online_done) = EExamResult::getAnalyticsStatusOnline($exam_id,$time_begin,$time_end);
			$result_1 = $this->renderPartial('application.views.eExam._online_realtime',array('result_online_realtime'=>$result_online_realtime,'online_realtime'=>$detail),true);
			$result_2 = $this->renderPartial('application.views.eExam._online_done',array('result_online_done'=>$result_online_done,'online_realtime'=>$detail),true);
			//Cap nhat nhung thang thi ma khong chiu nop bai
			EExamResult::updateTimeEndOnline($exam_id);
			//Kiem tra xem thanh vien co dang thi ko, hoac da hoan thanh bai thi chua
			if($user_id!=0)
			{
				$row_check = EExamResult::lastTurnExamByUser($exam_id,$user_id);
				if(!empty($row_check))
				{
					if($row_check['time_end']!=0) $check=1;
					$time_do_exam = $detail['time_do_exam']*60;
					if(time() > $row_check['time_start']+$time_do_exam) $check=1;
				}
				
			}
		}
		$output=array('result_1'=>$result_1,'result_2'=>$result_2,'check'=>$check);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
	}
	public function actionAutoUpdatePoint()
	{
		$exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;
		$rows = EExamResult::getExamResultOnlineDay();
		if(!empty($rows))
		{
			//Cap nhat trang thai
			$array_input["status"]=1;
			$array_key["status"]=0;
			$array_key["is_online_day"]=1;
			$array_key["exam_id"]=$exam_id;
			CommonModel::updateObjectNew($array_input,$array_key,'e_exam_result');
			//Cap nhat diem thanh tich, level		
			if($rows)
			foreach($rows as $row)
			{
				$user_id = $row['user_id'];
				$point = $row['point'];
				$user_point = EUserPoint::getUserPointByUser($user_id);
				$point_1 = isset($user_point["point_1"])? $user_point["point_1"]:0;
				$point_1 = $point_1 + $point;
				$level = Common::getLevelUserPoint($point_1);
				CommonModel::updateObjectNew(array("point_1"=>$point_1,'level'=>$level),array('user_id'=>$user_id),'e_user_point');
			}
			//Cap nhat trang thai cho de (tro ve de binh thuong)
			CommonModel::updateObjectNew(array("is_online_day"=>0),array('id'=>$exam_id),'e_exam');
			
		}
		echo "DONE!";
	}
	public function actionGoodQa()
    {
        if(!isset($_SESSION['userid']))
        {
            $error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247.<br>';
            echo $error;
            exit();
        }
        $sender_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$users = $this->users;
		$sender_name = $users['username'];
        $qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
		$qa_root_id=isset($_POST['qa_root_id']) ? intval($_POST['qa_root_id']) : 0;
        $is_good='good_'.$qa_id.$sender_id;
		$detail = EQa::getQaById2($qa_id);
		$detail_root = EQa::getQaById2($qa_root_id);//Cau hoi root
		$list_sub_id = $detail_root['sub_id'];
		$total_good = EQaLog::getTotalLogByUser($list_sub_id,$sender_id);
        if(!isset($_COOKIE[$is_good]))
        {
			$user_info = EUser::getUserById($detail['user_id']);
			$user_id = $user_info['id'];
			$username = $user_info['username'];
			$avatar = $user_info['avatar'];
			$fbid = $user_info['fbid'];
			$school_id = $user_info['school_id'];
			$school_name = $user_info['school_name'];
			$city_id = $user_info['city_id'];
			$city_name = $user_info['city_name'];
			$district_id = $user_info['district_id'];
			$district_name = $user_info['district_name'];
			$create_h = date('H');
			$create_day = date('d');
			$create_month = date('m');
			$create_year = date('Y');
			$create_date = date('Y-m-d');
			$create_time = time();
			if(($detail_root['user_id']==$sender_id && $total_good<3) || $detail_root['user_id']!=$sender_id)
			{
				$point_1 = 1;//Diem hai long
				$point_2 = 1;//Diem tich cuc
				$status = 0;
				if($detail_root['user_id']==$sender_id)
				{
					$point_2 = 3;
					$status = 1;
				}
				//Kiem tra xem nguoi hoi da hai long cau tra loi nay chua
				$check_good = EQaLog::getLogBySenderQa($detail_root['user_id'],$qa_id);
				if(!empty($check_good))
				{
					$status=1;
				}
				$ip = Common::getRealIpAddr();
				
				$array_input = array('qa_id'=>$qa_id,'cat_id'=>$detail['cat_id'],'point_1'=>$point_1,'point_2'=>$point_2,'status'=>$status,'ip'=>$ip,'sender_id'=>$sender_id,'sender_name'=>$sender_name,'user_id'=>$user_id,'username'=>$username,'avatar'=>$avatar,'fbid'=>$fbid,'school_id'=>$school_id,'school_name'=>$school_name,'city_id'=>$city_id,'city_name'=>$city_name,'district_id'=>$district_id,'district_name'=>$district_name,'create_h'=>$create_h,'create_day'=>$create_day,'create_month'=>$create_month,'create_year'=>$create_year,'create_date'=>$create_date,'create_time'=>$create_time);
				$ok = CommonModel::insertObject($array_input,'e_qa_log');
				if($ok>=0)
				{
					$expire=time()+3600;
					setcookie($is_good,$is_good,$expire,'/');
					echo 'Bạn đã Hài lòng câu trả lời này!';
				}
				//Cong diem thanh tich, diem hai long
				if($status==1)
				{
					EUserPointQa::updateUserPointQa($user_id,$point_1,$point_2);
				}
			}
        }
        else
        {
            echo 'Bạn đã Hài lòng câu trả lời này rồi!';
        }
        exit();
    }
	public function actionRecentQa()
	{
		$qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
		$type=isset($_POST['type']) ? intval($_POST['type']) : 1;//1: Hai long nhieu nhat, 2: Moi nhat
		$row = EQa::getQaById2($qa_id);
		$list_sub_id = $row['sub_id'];
		list($subs,$qa_img, $qa_like, $qa_like_user, $qa_good)=EQa::getRecentQa($qa_id,$type,$list_sub_id);
        $qa_is_good = array();
		if(isset($_SESSION['userid']) && $list_sub_id!='')
		{
			$qa_is_good = EQaLog::getTotalLogBySenderId($_SESSION['userid'],$list_sub_id);
		}
		$this->renderPartial("application.views.eQa._mod_qa_sub",
                array('row'=>$row,'subs'=>$subs, 'qa_img'=>$qa_img, 'qa_like'=>$qa_like,
					  'qa_like_user'=>$qa_like_user, 'qa_good'=>$qa_good, 'qa_is_good'=>$qa_is_good
        ));
	}
	public function actionTop5Qa()
	{
		$cat_id=isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
		$tops = EQaLog::getTop5QaMonth($cat_id);
		$this->renderPartial("application.views.eQa._top_sub",
                	   array('tops'=>$tops
        ));
	}
	public function actionSetQaShare()
	{
		$qa_id=isset($_POST['qa_id']) ? intval($_POST['qa_id']) : 0;
		$ok = EQa::updateQaShare($qa_id);
		echo $ok;
	}
	public function actionUploadExamFan()
	{
		if(!isset($_SESSION['userid']))
		{
			$error = 'Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			$status = 0;//Chua login
			$output=array('error'=>$error,'status'=>$status);
			$output=json_encode($output);
			header("Content-Type: application/json; charset=utf-8");
			echo $output;
			exit();
		}
        $user_id=isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$users = $this->users;
		$username = $users['username'];
		
        $title=isset($_POST['title']) ? mysql_escape_string($_POST['title']) : '';
		$alias = Common::generate_slug($title);
		$file_exam=isset($_POST['file_exam']) ? $_POST['file_exam'] : '';
		$description=isset($_POST['description']) ? mysql_escape_string($_POST['description']) : '';
		$introtext = Common::getIntrotext($description,200,'');
		$create_date = time();
		$edit_date = $create_date;
		$publish_date = $create_date;
		
		$array_input = array('title'=>$title, 'alias'=>$alias, 'file_original'=>$file_exam, 'description'=>$description, 'introtext'=>$introtext,'create_date'=>$create_date, 'edit_date'=>$edit_date, 'publish_date'=>$publish_date,'user_id'=>$user_id,'username'=>$username);
		
		$ok = CommonModel::insertObject($array_input, 'e_exam_fan');
		if($ok>=0)
		{
			$status = 1;
			$error = 'Cảm ơn bạn đã tham gia upload đề thi kiếm FAN!';
		}
		else
		{
			$status=0;
			$error = 'Có lỗi!';
		}
		$output=array('error'=>$error,'status'=>$status);
		$output=json_encode($output);
		header("Content-Type: application/json; charset=utf-8");
		echo $output;
		exit();
        exit();
	}
	
	public function actionPostExamAnswerFan()
	{
		if(!isset($_SESSION['userid']))
		{
			$error='Bạn chưa đăng nhập vào hệ thống Tuyensinh247';
			echo $error;
			exit();
		}
		$exam_fan_id=isset($_POST['exam_fan_id']) ? intval($_POST['exam_fan_id']) : 0;
		$answer_member=isset($_POST['answer_member']) ? $_POST['answer_member'] : '';
		$file_answer=isset($_POST['file_answer']) ? $_POST['file_answer'] : '';
        
		$users=$this->users;
		$username=$users['username'];
		$user_id=$users['id'];		
		$create_date=time();
		$edit_date=$create_date;
		$publish_date=$create_date;
		
		$array_input=array('exam_fan_id'=>$exam_fan_id,'user_id'=>$user_id,'username'=>$username,'answer_member'=>mysql_escape_string($answer_member),'file_answer'=>mysql_escape_string($file_answer),'create_date'=>$create_date,'edit_date'=>$edit_date,'publish_date'=>$publish_date,'status'=>0);
		$is_answer='answer_exam_fan_'.$exam_fan_id.$user_id;
        
		if(isset($_COOKIE[$is_answer]))
		{
			echo 'Cám ơn bạn đã đóng góp lời giải!';
			exit();
		}
		$ok = 0;
		if($answer_member=='' && $file_answer=='')
		{
			echo 'Vui lòng nhập nội dung lời giải!';
			exit();
		}
		else
		{
			$ok=CommonModel::insertObject($array_input,'e_exam_fan_answer');
		}
		if($ok>=0)
		{
			if(!isset($_COOKIE[$is_answer]))
			{
				$expire=time()+86400*365;
				setcookie($is_answer,$is_answer,$expire,'/');
			}
			
			echo 'Cảm ơn bạn. Chúng tôi sẽ duyệt lời giải của bạn sớm nhất có thể!';
		}
		else
		{
			echo 'Có lỗi trong quá trình xử lý';
		}
		exit();
	}
}
?>
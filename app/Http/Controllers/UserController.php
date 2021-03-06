<?php

    namespace App\Http\Controllers;


    use App\Post;
    use Illuminate\Http\Request;
    use App\User;

    class UserController extends Controller
    {
        //
        public function check($mod, $data_array)
        { //$mod为数据数组键名对应数据的正则, $data_array为数据数组
            foreach ($data_array as $key => $value) { //$data_array的键名在$mod数组中必有对应  若无请检查调用时有无逻辑漏洞
                if (!preg_match($mod[$key], $value)) {
                    return false; //此处数据有误
                }
            }

            return true;
        }

        public function msg($code, $msg)
        {
            $status = array(
                0 => '成功',
                1 => '缺失参数',
                2 => '账号密码错误',
                3 => '错误访问',
                4 => '未知错误',
                5 => '其他错误'
            );

            $result = array(
                'code' => $code,
                'status' => $status[$code],
                'data' => $msg
            );

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }

        private function chechUser($sid, $password)
        {
            $data = [];
            $jwxt_api_url = "https://api.sky31.com/edu-new/student_info.php";
            $jwxt_api_url = $jwxt_api_url . "?role=" . env('ROLE') . '&hash=' . env('HASH') . '&sid=' . urlencode($sid) . '&password=' . urlencode($password);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $jwxt_api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($output, true);
            if($data["code"] == 0) {
                return $data;
            }

            $portal_api_url = "https://api.sky31.com/PortalCode/edu-new/student_info.php";
            $portal_api_url = $portal_api_url . "?role=" . env('ROLE') . '&hash=' . env('HASH') . '&sid=' . urlencode($sid) . '&password=' . urlencode($password);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $portal_api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($output, true);
            return $data;
        }

        public function login(Request $request)
        {
            session(['login' => false, 'id' => null]);
            $mod = array(
                'stu_id' => '/^20[\d]{8,10}$/',
                'password' => '/^[^\s]{8,20}$/',
            );
            if (!$request->has(array_keys($mod))) {
                return $this->msg(1, __LINE__);
            }
            $data = $request->only(array_keys($mod));
            if (!$this->check($mod, $data)) {
                return $this->msg(3, '数据格式错误' . __LINE__);
            };
            $user = User::query()->where('stu_id', $data['stu_id'])->first();

            if (!$user) {
                // 该用户未在数据库中 用户名错误 或 用户从未登录
                //利用三翼api确定用户账号密码是否正确
                $output = $this->chechUser($data['stu_id'], $data['password']);

                if ($output['code'] == 0) {
                    $info = array(
                        'nickname' => '热心的路人甲',
                        'stu_id' => $data['stu_id'],
                        'password' => md5($data['password']),
                    );
                    $user = new User($info);
                    $result = $user->save();
                    if ($result) {
                        session(['login' => true, 'id' => $user->id]);
                        return $this->msg(0, $user->info());
                    } else {
                        return $this->msg(4, __LINE__);
                    }

                } else {
                    //失败
                    return $this->msg(2, __LINE__);
                }
            } else {
                if ($user->password === md5($data['password'])) {
                    session(['login' => true, 'id' => $user->id]);
                    return $this->msg(0, $user->info());
                } else {
                    $output = $this->chechUser($data['stu_id'], $data['password']);
                    if ($output['code'] == 0) {
                        $user->password = md5($data['password']);
                        $user->save();
                        session(['login' => true, 'id' => $user->id]);
                        return $this->msg(0, $user->info());
                    } else {
                        return $this->msg(2, __LINE__);
                    }
                }

            }

        }

        public function getUserInfo()
        {
            $user = User::query()->where('id', session('id'))->first();

            if ($user) {
                return $this->msg(0, $user->info());
            } else {
                return $this->msg(4, __LINE__);
            }
        }

        protected function saveAvatar(Request $request)
        {
            if (!$request->hasFile('avatar')) {
                return $this->msg(3, '文件格式错误');
            }
            $file = $request->file('avatar');
            $allow_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = $file->getClientOriginalExtension();
            if ($file->getSize() > 2048000) { //2M
                return $this->msg(3, '文件大小' . __LINE__);
            }
            if (in_array($extension, $allow_ext)) {
                $savePath = public_path() . '/upload/avatar';
                $filename = session('id') . '.jpg';
                $file->move($savePath, $filename);
//                compress($savePath."/".$filename);
                User::query()->where('id', session('id'))->update(['avatar' => $filename]);
                return $this->msg(0, '成功');
            } else {
                return $this->msg(3, '文件格式错误');
            }
        }


        public function updateUserInfo(Request $request)
        {
            $mod = array(
                'nickname' => '/^[^\s]{2,30}$/',
                'phone' => '/(^1[0-9]{10}$)|(^$)/',
                'qq' => '/(^[0-9]{5,13}$)|(^$)/',
                'wx' => '/(^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}$)|(^1[0-9]{10}$)|(^$)/',
                'class' => '/^[^\s]{5,60}$/'
            );
            if (!$request->has(['nickname'])) {
                return $this->msg(1, __LINE__);
            }

            $contact = $request->only(['qq', 'wx', 'phone']);
            if (!$contact['qq'] && !$contact['wx'] && !$contact['phone']) {
                return $this->msg(3, '???' . __LINE__);
            }

            $data = $request->only(array_keys($mod));
            if (!$this->check($mod, $data)) {
                return $this->msg(3, '数据格式错误' . __LINE__);
            };

            $user = User::query()->where('id', $request->session()->get('id'))->update($data);

            if ($user) {
                return $this->msg(0, null);
            } else {
                return $this->msg(4, '数据更新失败' . __LINE__);
            }
        }

        public function getUserPost()
        {
            $list = Post::query()->where('user_id', session('id'))->orderBy('updated_at', 'desc')->get();
            return $this->msg(0, $list);
        }

        public function manager_getUserPost(Request $request)
        {
            $list = Post::query()->where('user_id', $request->route('id'))->get()->toArray();
            return $this->msg(0, $list);
        }

        public function searchUser(Request $request)
        {
            if (!$request->has(['keyword'])) {
                return $this->msg(1, __LINE__);
            }

            $keyword = preg_replace("/^\xef\xbb\xbf/", '', $request->only('keyword')['keyword']);
            $keyword = json_decode($keyword, true);
            if (!is_array($keyword) || count($keyword) > 5) {
                return $this->msg(3, __LINE__);
            }
            $num_array = array(-1); //如果持续为空 会导致 下面的sql 出现 {`stu_id` IN ()} 小括号内为空的情况
            foreach ($keyword as $value) {
                if( preg_match('/^\d+$/', $value) ) {
                    array_push($num_array, $value);
                }
            }
            $str = join('|', $keyword);
			$num = join(',', $num_array);
            $result = User::query()->whereRaw("`nickname` REGEXP ?", array($str))
                ->orWhereRaw("`stu_id` IN ($num) or `id` IN ($num)")
                ->get(['id', 'nickname', 'class', 'wx', 'qq', 'phone', 'stu_id', 'black'])->toArray();

			return $this->msg(0, $result);
        }

        public function blackUser(Request $request)
        {
            $user = User::query()->where('id', $request->id)->first();
            $user->black = $user->black + 1;
            $result = $user->save();

            if ($result) {
                return $this->msg(0, null);
            } else {
                return $this->msg(4, '失败,咨询管理员' . __LINE__);
            }
        }

        public function unblackUser(Request $request)
        {
            $user = User::query()->where('id', $request->id)->first();
            $user->black = 0;
            $result = $user->save();

            if ($result) {
                return $this->msg(0, null);
            } else {
                return $this->msg(4, '失败,咨询管理员' . __LINE__);
            }
        }
    }

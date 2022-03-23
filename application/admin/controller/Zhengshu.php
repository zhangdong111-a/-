<?php


namespace app\admin\controller;


use think\Db;
use think\Request;

class Zhengshu extends Base
{
    public function index()
    {
        $key = input('key');
        $map = [];
        if ($key && $key !== "") {
            $map['kcname'] = ['like', "%" . $key . "%"];
        }
        $map['pid'] = 0;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('val', $key);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //购买课程人员
    public function renyuan()
    {
        $id = input('id');
        $map['cid'] = $id;
        $map['pay_status'] = 1;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('order')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('order')->where($map)->page($Nowpage, $limits)->select();
        foreach ($lists as $k => $v) {
            $v['kcname'] = Db::name('curriculum')->where('id', $v['cid'])->value(['kcname']);
            $v['studentname'] = Db::name('student')->where('id', $v['uid'])->value(['name']);
            $lists[$k] = $v;
        }
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('id', $id);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //子级课程
    public function ziji()
    {
        $id = input('id');
        $map['pid'] = $id;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('curriculum')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('curriculum')->where($map)->page($Nowpage, $limits)->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        if (input('get.page')) {
            return json($lists);
        }
        $this->assign('id', $id);
        return $this->fetch();
    }

    //证书列表
    public function zhengshu()
    {
        //课程id
        $id = input('id');
        $map['cid'] = $id;
        $Nowpage = input('get.page') ? input('get.page') : 1;
        $limits = config('list_rows');// 获取总条数
        $count = Db::name('zhengshu')->where($map)->count();//计算总页面
        $allpage = intval(ceil($count / $limits));
        $lists = Db::name('zhengshu')->where($map)->page($Nowpage, $limits)->order('id desc')->select();
        $this->assign('Nowpage', $Nowpage); //当前页
        $this->assign('allpage', $allpage); //总页数
        $this->assign('id', $id);
        if (input('get.page')) {
            return json($lists);
        }
        return $this->fetch();
    }

    //人员导入
    public function daoru()
    {
        $id = input('id');
        $this->assign('id', $id);
        if (request()->isPost()) {
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            //获取表单上传文件
            $file = request()->file('file');
            $info = $file->validate(['ext' => 'xls'])->move(ROOT_PATH . 'public/user');  //上传验证后缀名,以及上传之后移动的地址  E:\wamp\www\bick\public
            if ($info) {
                $exclePath = $info->getSaveName();  //获取文件名
                $file_name = ROOT_PATH . 'public/user' . DS . $exclePath;//上传文件的地址
                $objReader = \PHPExcel_IOFactory::createReader("Excel5");
                $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
                $excel_array = $obj_PHPExcel->getSheet(0)->toArray();   //转换为数组格式
                array_shift($excel_array);  //删除第一个数组(标题);
                $user = [];
                $i = 0;
                foreach ($excel_array as $k => $v) {
                    $user[$k]['uid'] = $v[0];
                    $user[$k]['kcname'] = $v[1];
                    $user[$k]['studentname'] = $v[2];
                    $user[$k]['cid'] = $id;
                    $user[$k]['fenshu'] = $v[4];
                    $i++;
                }
                $flag = Db::name("zhengshu")->insertAll($user);
                if ($flag > 0) {
                    return json(['code' => 1, 'msg' => '导入成功']);
                } else {
                    return json(['code' => 0, 'msg' => '导入失败']);
                }
            } else {
                echo $file->getError();
            }
        }
        return $this->fetch();
    }

    //导出
    public function daochu()
    {
        //课程id
        $cid = input('id');
        $w['cid'] = $cid;
        $w['pay_status'] = 1;
        $renyuan = Db::name('order')->field('cid,pay_status,uid')->where($w)->select();
        foreach ($renyuan as $k => $v) {
            $v['kcname'] = Db::name('curriculum')->where('id', $v['cid'])->value(['kcname']);
            $v['studentname'] = Db::name('student')->where('id', $v['uid'])->value(['name']);
            $v['phone'] = Db::name('student')->where('id', $v['uid'])->value(['phone']);
            $renyuan[$k] = $v;
        }
        $header = ['用户id', '课程名称', '学生姓名', '手机号', '分数'];
        $filename = '用户信息';
        $arr = [];
        foreach ($renyuan as $key => $value) {
            $arr[$key]['uid'] = $renyuan[$key]['uid'];
            $arr[$key]['kcname'] = $renyuan[$key]['kcname'];
            $arr[$key]['studentname'] = $renyuan[$key]['studentname'];
            $arr[$key]['phone'] = $renyuan[$key]['phone'];
            $arr[$key]['fenshu'] = 0;
        }
        $this->excelExport($filename, $header, $arr);
    }

    //添加证书
    public function add_zhengshu()
    {
        $id = input('id');
        $this->assign('id', $id);
        $kecheng = Db::name('curriculum')->where('id', $id)->find();
        $this->assign('kecheng', $kecheng);
        $student = Db::name('student')->select();
        $this->assign('student', $student);
        if (request()->isAjax()) {
            $param = input('param.');
            $data['cid'] = $param['cid'];
            $data['kcname'] = $param['kcname'];
            $student = Db::name('student')->where('id', $param['uid'])->find();
            $data['studentname'] = $student['name'];
            $data['uid'] = $param['uid'];
            $zs = Db::name('zhengshu')->where('cid = ' . $param['cid'] . ' and uid = ' . $param['uid'] . '')->count();
            if ($zs > 0) {
                return json(['code' => -1, 'msg' => '该学员已存在']);
            }
            $flag = Db::name('zhengshu')->insert($data);
            if ($flag > 0) {
                return json(['code' => 1, 'msg' => '添加成功']);
            } else {
                return json(['code' => -1, 'msg' => '添加失败']);
            }
        }
        return $this->fetch();
    }

    //编辑证书
    public function edit_zhengshu()
    {
        $id = input('id');
        $zhengshu = Db::name('zhengshu')->where('id', $id)->find();
        $this->assign('zhengshu', $zhengshu);
        if (request()->isAjax()) {
            $zs_id = input('id');
            $photo = input('photo');
            $flag = Db::name('zhengshu')->where('id', $zs_id)->update(['photo' => $photo]);
            if ($flag > 0) {
                return json(['code' => 1, 'msg' => '编辑成功']);
            } else {
                return json(['code' => -1, 'msg' => '编辑失败']);
            }
        }
        return $this->fetch();
    }

    //上传证书
    public function upload_zhengshu()
    {
        $id = input('id');
        $this->assign('id', $id);
        if (request()->isAjax()) {
            $cid = input('id');
            $photo = input('photo');
            $data['zhengshu_img'] = $photo;
            $flag = Db::name('curriculum')->where('id', $cid)->update($data);
            if ($flag > 0) {
                return json(['code' => 1, 'msg' => '上传成功']);
            } else {
                return json(['code' => 1, 'msg' => '上传失败']);
            }
        }
        return $this->fetch();
    }

    //删除证书
    public function del_zhengshu()
    {
        $id = input('id');
        $flag = Db::name('zhengshu')->where('id', $id)->delete();
        if ($flag > 0) {
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => -1, 'msg' => '删除失败']);
        }
    }

    public function excelExport($fileName = '', $headArr = [], $data = [])
    {
        vendor("PHPExcel.PHPExcel");
        $fileName .= "_" . date("Y_m_d H_i_s", Request::instance()->time()) . ".xls";
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getProperties();
        $key = ord("A"); // 设置表头
        foreach ($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { // 行写入
            $span = ord("A");
            foreach ($rows as $keyName => $value) { // 列写入
                $objActSheet->setCellValue(chr($span) . $column, $value);
                $span++;
            }
            $column++;
        }
        $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$fileName");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); // 文件通过浏览器下载
        exit();
    }


    public function tp()
    {
        $kcid = input('kcid');
        $kc = Db::name('curriculum')->where('id', $kcid)->find();
        $this->assign('kc', $kc);
        return $this->fetch();
    }

    public function xy()
    {
        $kcid = input('kcid');
        $xy = Db::name('zhengshu')->where('cid', $kcid)->select();
        foreach ($xy as $k => $v) {
            if ($v['fenshu'] <= 0) {
                $xy[$k]['fenshu'] = '';
            }
        }
        return json($xy);
    }

    public function zhengshuimg()
    {
        $id = input('id');
        $img = input('img');
        $reg = '/data:image\/(\w+?);base64,(.+)$/si';
        preg_match($reg, $img, $match_result);
        $file_name = date('Ymd') . date('His') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT) . '.png';
        $logo_path = ROOT_PATH . "public/uploads/zhengshu/";
        $image_file_path = date('Ymd');
        $url = $image_file_path . "/" . $file_name;
        $image_file = $logo_path . $url;
        if (!file_exists($logo_path . $image_file_path)) {
            mkdir(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'zhengshu' . DS . date('Ymd'));
        }
        $num = file_put_contents($image_file, base64_decode($match_result[2]));
        if ($num) {
            $arr = array("photo" => $url);
            Db::name('zhengshu')->where('id', $id)->update($arr);
        } else {
            return false;
        }
        return json(['code' => 1, 'msg' => '生成成功']);
    }

    public function zsff()
    {
        $id = input('id');
        $arr = array("status" => 1,"sj"=>date('Y-m-d'));
        $up = Db::name('zhengshu')->where("cid", $id)->update($arr);
        if ($up) {
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 2, 'msg' => '操作失败']);
        }
    }

}
<?php
namespace app\home\controller;

use app\home\model\Cart;
use app\home\model\Goods;
use app\home\model\Order;
use app\home\model\User;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
//        展示用户登录视图
        return view('index');
    }
    public function login(){
//        接收参数
        $param=input();
//        验证参数
        $rule = [
            'user_name'  =>  'require',
            'password' =>  'require',
        ];
        $result = $this->validate($param,$rule);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
//        跟表里的用户信息对比
        $data=User::where('user_name',$param['user_name'])
            ->where('password',md5($param['password']))
            ->find()->toArray();
//        查到说明登录成功
        if($data){
//            登录之后记住用户id
            session('user',$data);
            return redirect('goods');
//            否则登录失败
        }else{
           $this->error("用户名或密码不对");
        }
    }
    public function goods(){
//        查询商品表里的商品信息
        $data=Goods::select()->toArray();
        return view('goods',['data'=>$data]);
    }
    public function cart($id){
//        先判断用户是否登录
        if(!session('user')){
            $this->error("对不起，请先登录在添加商品");
        }
//        验证参数是否为数字
        if(!is_numeric($id)){
            $this->error("参数必须为数字");
        }
//        将商品添加到购物车表
//        登录的话取出用户id
        $user_id=session('user.user_id');
//        拼接数组
        $data=[
            'userid'=>$user_id,
            'goodsid'=>$id,
            'number'=>1
        ];
//        如果入的同一件商品那么就数量累加
        $cart=Cart::where('goodsid',$id)->where('userid',$user_id)->find();
        if($cart){
            Cart::update(['number'=>$data['number']+1],['userid'=>$user_id,'goodsid'=>$id]);
        }
//        将商品信息入库
        $data=Cart::create($data,true);
        if($data){
            return "<script>alert('商品添加成功，请去购物车查看');location.href='goods'</script>";
        }else{
            $this->error("商品添加失败");
        }
    }
    public function selcart(){
//        点击查看2购物车，展示详细信息
        $data=Cart::join('user','user_id=userid')
            ->join('goods','goods_id=goodsid')
            ->select()->toArray();
       return view('sel',['data'=>$data]);
    }
//    点加号，库里的库存也跟着改
    public function jia(){
        $param=input();
        //        验证参数
        $rule = [
            'number'  =>  'require',
            'cart_id' =>  'require',
        ];
        $result = $this->validate($param,$rule);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        Cart::update(['number'=>$param['number']],['cart_id'=>$param['cart_id']]);
    }
    public function order($id){
//        生成订单页面
        $data=Cart::join('user','user_id=userid')
            ->join('goods','goods_id=goodsid')
            ->where('cart_id',$id)
            ->find()->toArray();
        return view('order',['data'=>$data]);
    }
    public function addorder(){
        $param=input();
//       将订单信息添加入库
        $data=Order::create($param,true);
        return view('addorder',['data'=>$data]);
    }
}

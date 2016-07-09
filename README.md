第一个类weixinUser是一起做项目的朋友网上找的，这个类是用来在微信公众号里面登录的，订阅号好像没这个权限，得服务号才行。第二个类weChat_login是我自己写的，用于PC端的微信登录。
    

需要注意的是：这两个类的都有appid和AppSecret这两个参数，但是两两者有本质的不同：

 - weChat_login的appid和AppSecret是要从微信开放平台申请的.
 - weixinUser的appid和AppSecret是微信公众号的。

下面，我来用weChat_login这个类来做一个示例：

第一步，在login.php里面，重定向页面，让用户打开授权：

    
    include "path/weChat_login.php";
    header("Content-type: text/html; charset=utf-8");
    $AppID='';
    $AppSecret='';
    $redirect_uri=urlencode("http://yourdemain/oauth_callback.php");
    $login=new weChat_login($AppID,$redirect_uri,$AppSecret);
    $redi_uri=$_GET['redirect_uri'];
    $login->request_code();
    
其实就是重定向了一次，就像这里有我以前的一个项目示例，打开就可以登录：
[点击这里][1]


然后是oauth_callback.php,也就是回调一次，腾讯会将code发送给你：



    
    include "path/weChat_login.php";
    header("Content-type: text/html; charset=utf-8");
    $AppID='';
    $AppSecret='';
    $redirect_uri=urlencode("http://yourdemain/oauth_callback.php");
    $login=new weChat_login($AppID,$redirect_uri,$AppSecret);
    $redi_uri=$_GET['redirect_uri'];
    if(isset($_GET['code'])){
        $token=$login->get_token($_GET['code'],$_GET['state']);
        $accessToken=$login->get_access_token($token);
        if(!$login->is_effective($accessToken)){
            header("location:login.php");
        }
        $userInfo=$login->get_user_info($accessToken);
        setcookie('openid',$userInfo->{'openid'});
        setcookie('nickname',$userInfo->{'nickname'});
        setcookie('headimgurl',$userInfo->{'headimgurl'});
        print_r($userInfo);
    }
    
    


  [1]: http://open.weixin.qq.com/connect/qrconnect?appid=wx9e1dd96b27fd9fb7&redirect_uri=http://ssslol.com/oauth_callback.php&response_type=code&scope=snsapi_login&state=3d6be0a4035d839573b04816624a415e#wechat_redirect%22


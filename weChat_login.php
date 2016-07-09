<?php
/**
 * Created by PhpStorm.
 * User: pengfei-gao
 * Date: 16-6-7
 * Time: 上午10:07
 */

class weixinUser{

    protected $m_appid;//应用唯一标识，在微信开放平台提交应用审核通过后获得
    protected $m_AppSecret;//应用密钥AppSecret，在微信开放平台提交应用审核通过后获得
    function  __construct($appid, $m_AppSecret){
        //初始化
        $this->m_appid = $appid;
        $this->m_AppSecret = $m_AppSecret;

    }
    //根據用戶授權登錄之後渠道的code得到access_token
    public function get_access_token($code)
    {
        $access_token_url ="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->m_appid."&secret=".$this->m_AppSecret."&code=".$code."&grant_type=authorization_code";
        $access_token = json_decode(file_get_contents($access_token_url));
        if (isset($access_token->errcode)) {
            $this->error($access_token->errcode, $access_token->errmsg);
            return 0;
        } else {
            return $access_token;
        }

    }

    //根據accesss_token取到用戶的個人信息
    public function get_user_info($access_token, $language="zh_CN")
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token->{'access_token'}."&openid=".$access_token->{"openid"};
        $user_info = json_decode(file_get_contents($url));
        if (isset($user_info->errcode)) {
            $this->error($user_info->errcode,$user_info->errmsg);
            return 0;
        }else{
            return $user_info;
        }
    }
    //輸出錯誤信息
    public function error($errmsg, $errcode = "001")
    {
        echo '<h1>error：</h1>' . $errcode;
        echo '<br/><h2>error information：</h2>' . $errmsg;
    }


}

class weChat_login
{
    protected $m_token_url;
    protected $m_appid;//应用唯一标识，在微信开放平台提交应用审核通过后获得
    protected $m_AppSecret;//应用密钥AppSecret，在微信开放平台提交应用审核通过后获得
    protected $m_redirect_uri;//重定向地址，需要进行UrlEncode
    protected $m_response_type;//填code
    protected $m_scope;//应用授权作用域，拥有多个作用域用逗号（,）分隔，网页应用目前仅填写snsapi_login即可
    protected $m_state;//用于保持请求和回调的状态，授权请求后原样带回给第三方。该参数可用于防止csrf攻击（跨站请求伪造攻击），建议第三方带上该参数，可设置为简单的随机数加session进行校验

    /**
     * weChat_login constructor.
     * @param $appid
     * @param $redirect_uri
     * @param $app_secret
     * @param string $response_type
     * @param string $scope
     * @param string $state
     */
    function __construct($appid, $redirect_uri, $app_secret, $response_type = 'code', $scope = 'snsapi_login', $state = '1')
    {
        //初始化
        $this->m_appid = $appid;
        $this->m_redirect_uri = urlencode($redirect_uri);
        $this->m_response_type = $response_type;
        $this->m_scope = $scope;
        $this->m_state = $state;
        $this->m_AppSecret = $app_secret;
    }

    /**
     *重定向，通过用户客户端向服务器请求code
     */
    public function request_code()
    {
        header("location:https://open.weixin.qq.com/connect/qrconnect?appid=$this->m_appid&redirect_uri=$this->m_redirect_uri&response_type=$this->m_response_type&scope=$this->m_scope&state=$this->m_state#wechat_redirect
");
    }

    /**通过code获取access_token
     * @param $code =$_GET['code']
     * @param $state =$_GET['state']
     * @return int|mixed 如果出现异常，返回0和1：
     * 0：code获取失败
     * 1：在获取access_token时出现错误
     * 如果一切正常，则返回一个token对象，该对象有如下成员成员：
     * access_token 接口调用凭证
     * expires_in access_token 接口调用凭证超时时间，单位（秒）
     * refresh_token 用户刷新access_token
     * openid 授权用户唯一标识
     * scope 用户授权的作用域，使用逗号（,）分隔
     */
    public function get_token($code, $state)
    {
        if (empty($code)) {
            $this->error('授权失败,在获取code时出现异常');
            return 0;
        } else {
            $this->m_token_url =
                "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->m_appid&secret=$this->m_AppSecret&code=$code&grant_type=authorization_code";
            $token = json_decode(file_get_contents($this->m_token_url));
            if (isset($token->errcode)) {

                return 1;
            } else {
                return $token;
            }
        }
    }

    /**
     * @param $token 即get_token()方法返回的token对象
     * @return int|mixed 若出现异常，则返回0，否则返回一个access_token对象，该对象有如下成员
     * access_token 接口调用凭证
     * expires_in access_token 接口调用凭证超时时间，单位（秒）
     * refresh_token 用户刷新access_token
     * openid 授权用户唯一标识
     * scope 用户授权的作用域，使用逗号（,）分隔
     */
    public function get_access_token($token)
    {
        $access_token_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=' . $this->m_appid . '&grant_type=refresh_token&refresh_token=' . $token->refresh_token;
        $access_token = json_decode(file_get_contents($access_token_url));
        if (isset($access_token->errcode)) {
            $this->error($access_token->errcode, $access_token->errmsg);
            return 0;
        } else {
            return $access_token;
        }

    }

    /**检测access_token是否有效
     * @param $access_token 即由get_access_token()方法返回的access_token对象
     * @return bool
     */
    public function is_effective($access_token)
    {
        $url = "https://api.weixin.qq.com/sns/auth?access_token=$access_token->access_token&openid=$access_token->openid";
        $result = $access_token = json_decode(file_get_contents($url));
        if ($result->errcode == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $access_token 调用凭证
     * @param string $language 国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN
     * @return int|mixed 若出现异常，则返回0或1，说明如下：
     * 0：用户信息获取异常
     * 1：access_token 失效
     * 若一切正常，则返回一个user_info对象，该对象有如下成员：
     * openid       普通用户的标识，对当前开发者帐号唯一
     * nickname     普通用户昵称
     * sex          普通用户性别，1为男性，2为女性
     * province     普通用户个人资料填写的省份
     * city         普通用户个人资料填写的城市
     * country      国家，如中国为CN
     * headimgurl   用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     * privilege    用户特权信息，json数组，如微信沃卡用户为（chinaunicom）
     * unionid      用户统一标识。针对一个微信开放平台帐号下的应用，同一用户的unionid是唯一的。
     * +++++++++++++++++++++++++++++++
     * 建议：最好保存用户unionID信息，以便以后在不同应用中进行用户信息互通。
     */
    public function get_user_info($access_token, $language="zh_CN")
    {
        if ($this->is_effective($access_token)) {
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token->access_token&openid=$access_token->openid&lang=$language";
            $user_info = json_decode(file_get_contents($url));
            if (isset($user_info->errcode)) {
                $this->error($user_info->errcode,$user_info->errmsg);
                return 0;
            }else{
                return $user_info;
            }
        } else {
            $this->error("access_token 失效");
            return 1;
        }
    }

    /**
     * @param $errmsg 错误信息
     * @param int $errcode 错误码
     */
    public function error($errmsg, $errcode = "001")
    {
        echo '<h1>error：</h1>' . $errcode;
        echo '<br/><h2>error information：</h2>' . $errmsg;
    }


}


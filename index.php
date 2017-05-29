<?php
/**
 * 禁用或清除Cookies后还原Session Id示例
 * @author ImDong <www@qs5.org>
 * @copyright 2017 ImDong
 */


//**** 简易路由控制 ****//
    $cmd = 'cmd_' . (empty($_GET['cmd']) ? 'index' : strtolower($_GET['cmd']));
    function_exists($cmd) ? call_user_func($cmd) : die('/* cmd Error! */');

//**** 控制器定义 ****//

    /**
     * 默认首页
     * @return [type] [description]
     */
    function cmd_index()
    {
        session_start();
        $session_id = session_id();

        // 保存首次访问时间
        empty($_SESSION['user_time']) && $_SESSION['user_time'] = date('Y/m/d H:i:s');

        // 生成模板替换标记
        $tpl_tag = array(
            'session_id' => $session_id,
            'view_time'  => date('Y/m/d H:i:s')
        );

        // 调用输出模板
        view_tpl('index.html', $tpl_tag);
    }

    /**
     * 设置名称
     * @return [type] [description]
     */
    function cmd_set_name()
    {
        session_id($_REQUEST[session_name()]);
        session_start();
        $_SESSION['user_name'] = empty($_POST['name']) ? '' : $_POST['name'];
        header("Location: ./");
    }

    /**
     * 获取名称
     * @return [type] [description]
     */
    function cmd_get_name()
    {
        session_id($_REQUEST[session_name()]);
        session_start();
        $user_info = array(
            'session_id' => session_id(),
            'username' => empty($_SESSION['user_name']) ? 'Not Set' : $_SESSION['user_name'],
            'usertime' => empty($_SESSION['user_time']) ? 'now' : $_SESSION['user_time'],
        );
        die(json_encode($user_info));
    }

    /**
     * 获取用户Session Id
     * @return [type] [description]
     */
    function cmd_get_session_id()
    {
        // 从不同的途径获取session_id
        $session_id_etag = etag_get_id();
        $session_name = session_name();
        $session_id_cookie = empty($_COOKIE[$session_name]) ? false : $_COOKIE[$session_name];
        // 获取到 ETag 且与 Cookies 一致
        if($session_id_etag && $session_id_etag == $session_id_cookie){
            // 表示没改变
            header('HTTP/1.1 304 Not Modified');
            exit;
        } else
        // 获取到 ETag
        if($session_id_etag){
            // 设置到Cookies
            setcookie($session_name, $session_id_etag, 0, '/');
        }

        // 获取session_id
        $session_id = $session_id_etag ?: $session_id_cookie;

        // 设置 ETag
        $session_id && etag_set_id($session_id);

        // 生成模板替换标记
        $tpl_tag = array(
            'session_id' => $session_id
        );

        // 调用输出模板
        view_tpl('get_session_id.js', $tpl_tag);
    }

//**** 功能方法定义 ****//

    /**
     * 尝试从etag中获取ID
     */
    function etag_get_id()
    {
        // 没有记录
        if(!empty($_SERVER['HTTP_IF_NONE_MATCH'])){
            if(!preg_match('#^W/"(?<id>[^-]+)#', $_SERVER['HTTP_IF_NONE_MATCH'], $tag_info))
                return false;
            return $tag_info['id'];
        } else
        if(!empty($_SERVER['HTTP_SESSION_ID'])){
            return $_SERVER['HTTP_SESSION_ID'];
        } else {
            return false;
        }
    }

    /**
     * 设置etag id
     */
    function etag_set_id($session_id = false)
    {
        // 获取session_id做为缓存信息
        $session_id = $session_id ?: (empty($_COOKIE[session_name()]) ? '' : $_COOKIE[session_name()]);

        // 生成eTag
        $e_rand = rand(1000, 9999) * 100000;
        $e_time = time() - strtotime('2017/01/01');
        $e_hash = dechex($e_rand + $e_time);
        $e_tag = "W/\"{$session_id}-{$e_hash}\"";

        // 设置协议头
        $setHeader = array(
            'Content-Type'  => 'application/javascript',
            'ETag'          => $e_tag,
        );

        // 循环设置Header头
        foreach ($setHeader as $headerName => $headerValue){
            header(is_int($headerName) ? $headerValue : "{$headerName}: {$headerValue}");
        }

        return $e_tag;
    }

    /**
     * 生成并输出模板
     * @param  string $tpl_name 模板名
     * @param  arrray $tpl_tag  替换标记
     */
    function view_tpl($tpl_name, $tpl_tag = array())
    {
        // 获取模板内容
        $tpl_filename = sprintf('./tpl/%s', $tpl_name);
        $tpl_body = file_exists($tpl_filename) ? file_get_contents($tpl_filename) : 'tpl file not exists.';

        // 生成模板替换表
        $rep_arr = array('search' => array(), 'replace' => array());
        foreach ($tpl_tag as $key => $value) {
            $rep_arr['search'][] = sprintf('{$php.%s}', $key);
            $rep_arr['replace'][] = $value;
        }
        $tpl_body = str_replace($rep_arr['search'], $rep_arr['replace'], $tpl_body);
        die($tpl_body);
    }

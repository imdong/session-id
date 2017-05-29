# session-id
禁用清理Cookies后还原Session Id示例

在线演示示例: https://www.qs5.org/tools/session_id/

我的博客: https://www.qs5.org/

# Nginx.conf
```
server  
    {  
        # session_id 获取  
        rewrite ^/session_id.js /session_id/index.php?cmd=get_session_id last;  
    }
```

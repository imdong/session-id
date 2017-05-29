# session-id
禁用清理Cookies后还原Session Id示例

# Nginx.conf
```
server  
    {  
        # session_id 获取  
        rewrite ^/session_id.js /session_id/index.php?cmd=get_session_id last;  
    }
```

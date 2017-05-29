(function(session_id, script_list) {
    if(session_id.length <= 0){
        var script_obj = script_list[script_list.length - 1];
        session_id = 'string' == typeof script_obj.dataset.sessionId ? script_obj.dataset.sessionId : ('string' == typeof session_id ? session_id : false);
        if(session_id.length > 0){
            xhr = new XMLHttpRequest();
            xhr.open('GET', script_obj.src, true);
            xhr.setRequestHeader('Session-Id', session_id);
            xhr.send();
        }
    }
    window.session_id = session_id;
})('{$php.session_id}', document.getElementsByTagName('script'));

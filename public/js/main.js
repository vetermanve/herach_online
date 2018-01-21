/**
 * Created by vetermanve on 22/12/2017.
 */

var transportProto = {
    REST         : 'rest',
    PAGE         : 'page',
    connections  : {},
    meta         : {},
    setConnection: function (connection, type) {
        type = type || this.REST;
        this.connections[type] = connection
    },
    call         : function (method, resource, data, success, error) {
        this.connections[this.REST].call(method, resource, data, success, error);
    },
    loadPage     : function (method, resource, data, success, error) {
        this.connections[this.PAGE].call(method, resource, data, success, error);
    }
};

var ajaxConnection = {
    host : 'http://localhost/rest/',
    type : 'json',
    init : function (meta) {
        this.host = meta['host'] || this.host;
    },
    call : function (method, resource, data, success, error) {
        var self = this;
        $.ajax({
            url : self.host + '/' + resource,
            cache: false,
            type: method,
            dataType: self.type,
            data: data,
            success: function (data) {
                self.log('successful ' + method +  ':' + resource, data);
                success && success(data);
            },
            error: function (data) {
                self.log('error ' + method +  ':' + resource, data);
                error && error(data);
            }
        });
    },
    log : function (text, data) {
        if(data) {
            console.log('ajax: ' + text , data);
        } else {
            console.log('ajax: ' + text);
        }
    }
};

var setupForm = function(obj, resource, success, error, method, beforeSend) {
    method = method || 'post';
    var frm = $(obj);
    frm.submit(function(e) {
        var data = frm.serializeArray();
        e.preventDefault();
        if (beforeSend) {
            try {
                data = beforeSend(data) || data;
            } catch (e) {
                console.error(e);
            }
        }
        transport.call(method, resource, data, success, error);
    });
};

var nav = {
    go : function (page) {
        this.location.pathname = document.location.href = page;  
    }
};
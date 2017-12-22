/**
 * Created by vetermanve on 22/12/2017.
 */

var transportProto = {
    connection : {},
    meta : {},
    setConnection : function (connection) {
        this.connection = connection
    },
    call : function (method, resource, data, success, error) {
        this.connection.call(method, resource, data, success, error);
    }
};

var ajaxConnection = {
    host : 'http://localhost/rest/',
    init : function (meta) {
        this.host = meta['host'] || this.host;
    },
    call : function (method, resource, data, success, error) {
        var self = this;
        $.ajax({
            url : self.host + '/' + resource,
            cache: false,
            type: method,
            dataType: 'json',
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

var setupForm = function(obj, resource, success, error) {
    var frm = $(obj);
    console.log(frm);
    frm.submit(function(e) {
        e.preventDefault();
        transport.call('post', resource, frm.serializeArray(), success, error);
    });
};
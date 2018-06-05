/**
 * Created by vetermanve on 05/01/2018.
 */
var autoSave = {
    catchClass : '.auto-save',
    prefix : '',
    init : function () {
        console.log('Init autosave');
        
        var self = this;
        self.prefix = document.location.toString().split("?")[0];
        var body = $('body');
        
        body.bind("DOMSubtreeModified", this.checkRestore.bind(this));

        body.on('keyup', 'input', this.save.bind(this));
        body.on('keyup', 'textarea', this.save.bind(this));
    },
    checkRestore : function ()
    {
        var self = this;
        $(this.catchClass).each(function (id, obj_raw) {
            var obj = $(obj_raw);
            var name = obj.attr('name');
            if (name)
            {
                obj.ready(function (){
                    self.restore(obj, name);
                });
            }
        });
    },
    save : function(e) {
        var obj = $(e.target);
        var name = obj.attr('name');
        if (name && obj.has(this.catchClass)) {
            console.log('Autosave: ' + name);
            localStorage.setItem(this.getKey(name), obj.val());
        }
        return true;
    },
    restore : function (obj) {
        console.log('Restore', obj);
        obj = $(obj).first();
        var name = obj.attr('name');
        if (name && obj.has(this.catchClass)) {
            var data = localStorage.getItem(this.getKey(name));
            if (typeof (data) === 'string' && obj.val() === '') {
                obj.val(data);
                console.log('Restore autosave "' + name + '" : ' + data);
            }
        }
    },
    getKey : function (name) {
        return "AUTOSAVE_"  + this.prefix + "_" + name;
    },
    clear : function (obj) {
        var self = this;
        $(obj).find('.' + this.catchClass).each(function (id, obj_raw) {
            var obj = $(obj_raw);
            var name = obj.data('name');
            
            if (name) {
                localStorage.removeItem(self.getKey(name));
            }
        });
    }
};

autoSave.init();

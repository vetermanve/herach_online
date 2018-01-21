/**
 * Created by vetermanve on 05/01/2018.
 */
var autoSave = {
    catchClass : 'auto-save',
    prefix : '',
    init : function () {
        var self = this;
        self.prefix = document.location.toString().split("?")[0];
        $('.' + this.catchClass).each(function (id, obj_raw) {
            var obj = $(obj_raw);
            var name = obj.attr('name');
            if (name) {
                obj.keyup(function() {self.save(obj, name)});
                obj.ready(function() {self.restore(obj, name)});    
            }
        });
    },
    save : function(obj, name) {
        console.log('save');
        localStorage.setItem(this.getKey(name), $(obj).val());
    },
    restore : function (obj, name) {
        var data = localStorage.getItem(this.getKey(name));
        if (typeof (data) === 'string') {
            $(obj).val(data);
            console.log('restore ' + name + ' : ' + data);
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

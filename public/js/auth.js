/**
 * Created by vetermanve on 07/06/2018.
 */

var auth = new Vue({
    el: '#login',
    data: {
        loaded: false,
        authorized: false,
        user: {}
    },
    methods: {
        checkAuthorisation: function ()
        {
            var self = this;

            transport.call('get', 'rest/auth-user', {}, function (data)
            {
                if (typeof data.user_id !== 'undefined' && !!data.user_id)
                {
                    transport.call('get', 'rest/user', {id: data.user_id}, function (user)
                    {
                        self.user = user;
                        self.authorized = true;
                        self.loaded = true;
                    });
                } else
                {
                    self.authorized = false;
                    self.loaded = true;
                }
            }, function ()
            {
                self.loaded = true;
            });
        },
        login: function ()
        {
            var self = this;
            transport.call('get', 'rest/auth-user', {}, function (data)
            {
                if (typeof data.user_id !== 'undefined' && !!data.user_id)
                {
                    transport.call('get', 'rest/user', {id: data.user_id}, function (user)
                    {
                        $("#user-page a").text(user.nickname).attr('href', '/user/' + data.user_id);
                        $("#sign-in").hide();
                        $("#login").show();
                    })
                } else
                {
                    $("#sign-in").show();
                    $("#user-page").hide();
                    $("#login").show();
                }
            });
        }
    },
    created: function ()
    {
        this.checkAuthorisation();
    }
});

console.log('auth loaded');
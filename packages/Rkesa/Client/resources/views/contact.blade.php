<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ \App\GlobalSettings::first()->site_name }}</title>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link rel="shortcut icon" href="/ico.png"/>
    <link rel="stylesheet" href="{{ mix('/css/loader.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="/css/colors.css">
    <link rel="stylesheet" href="{{ mix('/css/application.css') }}">
    <link rel="stylesheet" href="{{ mix('/css/style.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/diga-chat@0.1.25/dist/diga-chat.css">
    {!! \App\GlobalSettings::first()->head !!}
    <script src="https://unpkg.com/vue@2.6.10/dist/vue.js"></script>
    <script src="https://unpkg.com/vuex@2.3.1/dist/vuex.js"></script>
    <script src="https://unpkg.com/vue-i18n@8.14.0/dist/vue-i18n.js"></script>
    <script src="https://unpkg.com/moment@2.24.0/moment.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/5.0/pusher.min.js"></script>
    {{-- <script src="https://unpkg.com/diga-chat@0.1.26/dist/DigaChat.umd.js"></script> --}}
    {{-- <script src="https://unpkg.com/diga-chat@1.0.53/dist/DigaChat.umd.js"></script> --}}
    <script src="https://unpkg.com/diga-chat@1.0.62/dist/DigaChat.umd.js"></script>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>

    <style type="text/css">
        .login-form {
            width: 340px;
            margin: 50px auto;
        }
        .login-form .login-box {
            margin-bottom: 15px;
            background: #f7f7f7;
            padding: 30px;
        }
        .login-form h2 {
            margin: 0 0 15px;
        }
        .form-control, .btn {
            min-height: 38px;
            border-radius: 2px;
        }
        .btn {        
            font-size: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
<div id="erp-application" class="page-wrapper" style="height: 100vh;">
    <digachat v-if="chat"
        ref="chat"
        :online="$root.online"
        :user="user"
        :users="users"
        :users_by_id="usersById"
        :chats_index_url="chatUrl"
        :chat_make_as_read_url="'/api/chat_messages/:id/make_as_read'"
        :chat_send_message_url="postChatMessagesUrl"
        :chat_messages_url="chatMessagesUrl"
        :chats_new_chat_url="'/api/chats'"
        :can_create_new_chats="false"
        :chat_send_files_url="uploadsUrl">
    </digachat>

    <div v-if="!chat" class="btn-toolbar" style="margin-left: auto; margin-right: auto; display: block;">
        <div class="btn-group mr-2" role='group' aria-label='First group' style="width: 100%; display: grid; grid-template-columns: 33.3% 33.3% 33.4%;">
            <button class="btn btn-secondary" v-on:click="changeLang('ru')">
                Русский
            </button>
            <button class="btn btn-secondary" v-on:click="changeLang('en')">
                English
            </button>
            <button class="btn btn-secondary" v-on:click="changeLang('pt')">
                Portugues
            </button>
        </div>
    </div>

    <div class="login-form" v-if="is_login && !is_authenticated">
        <div class="form login-box">
            <h2 class="text-center">@{{$t('authorization')}}</h2>       
            <div class="form-group">
                <input @@input="login_error = false" v-model="email" type="email" class="form-control" placeholder="someone@example.com" required="required">
            </div>
            <div class="form-group">
                <input @@input="login_error = false" v-model="password" type="password" class="form-control" :placeholder="$t('password')" required="required">
            </div>
            <p v-if="login_error" style="color: red;">@{{$t('login_error')}}</p>
            <div class="form-group">
                <button :disabled="!email || !password" v-on:click="login" class="btn btn-diga btn-block">@{{$t('login')}}</button>
            </div> 
        </div>
        <p class="text-center"><a v-on:click="changeForm(false)" href="#">@{{$t('registration')}}</a></p>
    </div>
    <div class="login-form" v-if="!is_login && !is_authenticated">
        <div class="form login-box">
            <h2 class="text-center">@{{$t('registration')}}</h2>       
            <div class="form-group">
                <input @@input="register_error = false" v-model="email" type="email" class="form-control" placeholder="someone@example.com" required="required">
            </div>
            <div class="form-group">
                <input @@input="register_error = false" v-model="name" name="name" type="text" class="form-control" placeholder="John Doe" required="required">
            </div>
            <div class="form-group">
                <input @@input="register_error = false" v-model="password" type="password" class="form-control" :placeholder="$t('password')" required="required">
            </div>
            <div class="form-group">
                <input @@input="register_error = false" v-model="password_confirm" type="password" class="form-control" :placeholder="$t('repeat_password')" required="required">
            </div>
            <div class="form-check">
                <input @@input="register_error = false" v-model="agree_with_policy" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="defaultCheck1">
                    @{{$t('agree_with_policy')}}
                  </label>
            </div>
            <div class="form-check">
                <input @@input="register_error = false" v-model="agree_with_mailing" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="defaultCheck1">
                    @{{$t('agree_with_mailing')}}
                  </label>
            </div>
            <p v-if="register_error" style="color: red;">@{{$t('register_error')}}</p>
            <div class="form-group">
                <button :disabled="!email || !password || !name || !password_confirm || password !== password_confirm || agree_with_policy === false" v-on:click="register" class="btn btn-diga btn-block">@{{$t('register')}}</button>
            </div> 
        </div>
        <p class="text-center"><a v-on:click="changeForm(true)" href="#">@{{$t('authorization')}}</a></p>
    </div>
    <div class="login-form" v-if="is_authenticated && !chat">
        <div class="form login-box">
            <h3 class="text-center">@{{$t('continue_as')}}@{{authenticated_username}}?</h3>       
            <div class="form-group">
                <button v-on:click="openchat" class="btn btn-diga btn-block">@{{$t('continue')}}</button>
            </div> 
            <div class="form-group">
                <button v-on:click="logoff" class="btn btn-danger btn-block">@{{$t('exit')}}</button>
            </div> 
        </div>
    </div>

</div>

<script type="module">

    window.token = {!! json_encode($token) !!}; 

    var pusher = new Pusher('d27fc13766ed436d16b1', {
      cluster: 'eu',
      forceTLS: true
    });

    // window.Echo = new Echo({
    //     broadcaster: 'pusher',
    //     key: process.env.MIX_PUSHER_KEY,
    //     cluster: 'eu',
    //     encrypted: true,
    //     auth: {
    //         headers: {
    //             Authorization: bearer,
    //         },
    //     },
    // });

    const store = new Vuex.Store({
        state: {
            count: 0
        },
        mutations: {
            increment (state) {
                state.count++
            }
        }
    });

    Vue.use(VueI18n);

    Vue.prototype.$bus = new Vue();

    let locales = {
        'ru': {
            'authorization': 'Авторизация', 
            'login': 'Войти', 
            'registration': 'Регистрация', 
            'register': 'Зарегистрироваться', 
            'password': 'Пароль', 
            'repeat_password': 'Повторите пароль',
            'continue_as': 'Продожить как ',
            'continue': 'Продожить',
            'exit': 'Выйти',
            'login_error': 'Ошибка. Неправильный логин или пароль.',
            'register_error': 'Ошибка. Логин уже использовался по этой ссылке.',
            'agree_with_policy': 'Нажимая на кнопку регистрации, вы подтверждаете согласие с политикой конфиденциальности и обработки данных.',
            'agree_with_mailing' : 'Согласен на получение рассылки',
        },
        'en': {
            'authorization': 'Authorization', 
            'login': 'Login', 
            'registration': 'Registration', 
            'register': 'Register', 
            'password': 'Password', 
            'repeat_password': 'Repeat password',
            'continue_as': 'Continue as ',
            'continue': 'Continue',
            'exit': 'Logout',
            'login_error': 'Error. Incorrect login or password.',
            'register_error': 'Error. Login already been used with current link.',
            'agree_with_policy': 'By clicking on the register button, You confirm that you are agree with the processing of personal data and privacy policy.',
            'agree_with_mailing' : 'I agree to receive promotional information',
        },
        'pt': {
            'authorization': 'Autorização', 
            'login': 'Entrar', 
            'registration': 'Registo', 
            'register': 'Registo', 
            'password': 'Senha', 
            'repeat_password': 'Repita a senha',
            'continue_as': 'Prossiga como ',
            'continue': 'Prossiga',
            'exit': 'Sair',
            'login_error': 'Erro. Login ou senha incorretos.',
            'register_error': 'Erro. O login já foi usado com o link atual.',
            'agree_with_policy': 'Ao clicar na tecla Registo, informo que concordo com o processamento de dados pessoais e política de privacidade.',
            'agree_with_mailing' : 'Aceito receber informações promocionais',
        },
    };

    const i18n = new VueI18n({
        locale: 'pt',
        messages: locales,
    });

    Vue.use(DigaChat, { store, i18n });

    var app = new Vue({
        store,
        i18n,
        el: '#erp-application',
        components: { 'digachat': DigaChat },
        props: [],
        data: {
            token: '',
            is_login: true,
            is_authenticated: false,
            email: "",
            name: "",
            password: "",
            password_confirm: "",
            hash: "",
            authenticated_username: "",
            authenticated_userid: 0,
            chat: false,
            users: [],
            login_error: false,
            register_error: false,
            agree_with_policy: true,
            agree_with_mailing: true,
            locale: 'pt',
        },
        mounted(){
            this.token = window.token;
            this.hash = window.localStorage.getItem("hash");
            if (this.hash)
            {
                axios.get('/api/service_referrer/check_hash/' + this.hash).
                then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.is_authenticated = true;
                        this.authenticated_username = window.localStorage.getItem("email");
                        this.authenticated_userid = window.localStorage.getItem("id");
                        this.listenPusher();
                    }
                });
            }
        },
        methods: {
            changeForm(value){
                this.email = "";
                this.password = "";
                this.password_confirm = "";
                this.name = "";
                this.is_login = value;
            },
            register(){
                if (this.password === this.password_confirm){
                    axios.post('/api/service_referrer/register', 
                    {
                        "email": this.email, 
                        "password": this.password, 
                        "name": this.name, 
                        "access_token": this.token,
                        'agree_with_mailing': this.agree_with_mailing,
                        'locale': this.locale,
                    }).
                    then(res => {
                        if (res.data.errcode == 1) {
                            // this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            this.register_error = true;
                        } else {
                            this.is_authenticated = true;
                            
                            this.hash = res.data.hash;
                            this.authenticated_username = res.data.email;
                            this.authenticated_userid = res.data.id;
                            window.localStorage.setItem("hash", res.data.hash);
                            window.localStorage.setItem("email", res.data.email);
                            window.localStorage.setItem("id", res.data.id);
                            this.listenPusher();
                        }
                    }, 
                    res => {
                        this.register_error = true;
                    }
                );
                }
            },
            login(){
                if (this.email && this.password){
                    axios.post('/api/service_referrer/login', {"email": this.email, "password": this.password, "access_token": this.token}).
                    then(res => {
                        if (res.data.errcode == 1) {
                            this.login_error = true;
                            // this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.is_authenticated = true;

                            this.hash = res.data.hash;
                            this.authenticated_username = res.data.email;
                            this.authenticated_userid = res.data.id;
                            window.localStorage.setItem("hash", res.data.hash);
                            window.localStorage.setItem("email", res.data.email);
                            window.localStorage.setItem("id", res.data.id);
                            this.listenPusher();
                        }
                    },
                        res => {
                            this.login_error = true;
                        }
                    );
                }               
            },
            logoff(){
                window.localStorage.clear();
                location.reload();
            },
            openchat(){
                this.getUsers();
                this.chat = true;
            },
            getUsers(){
                axios.get('/api/service_referrer/users/' + this.token).
                then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.users = res.data.users;
                    }
                });
            },
            changeLang(loc){
                i18n.locale = loc;
                this.locale = loc;
            },
            listenPusher(){
                var channel = pusher.subscribe(this.pusher_user_channel_name);
                channel.bind('ChatMessageEvent', function(e) 
                {
                    if ($this.$store.getters.getActiveDialog && $this.$store.getters.getActiveDialog.id === e.message.chat_id){
                        $this.$store.commit('addMessageToActiveDialog', {...e.message, message: ChatHelper.format_message(e.message.message)});
                        $this.$bus.$emit('scrollToBottom');
                        $this.$store.dispatch('readMessage', e.message.id);
                    } else {
                        $this.$store.commit('dialogAddNotRead', e.message.chat_id);
                        let dialog = $this.$store.getters.getDialogs.find(d => d.id === e.message.chat_id);
                        $this.$toastr.s(ChatHelper.format_message(e.message.message), $this.$refs.chat.get_dialog_name(dialog));
                    }
                });

                channel.bind('ChatCreatedEvent', function(e) 
                {
                    $this.$store.commit('addDialog', e.chat);
                });
            }
        },
        computed: {
            user(){
                return {"id": parseInt(this.authenticated_userid), "name": this.authenticated_username};
            },
            usersById(){

                var obj = new Object();

                this.users.forEach(u => {
                    obj[u.id] = u;
                });

                return obj;
            },
            chatUrl(){
                return '/api/service_referrer_chat/chats/' + this.token;
            },
            chatMessagesUrl(){
                return '/api/service_referrer_chat/' + this.hash + '/:id/messages';
            },
            postChatMessagesUrl(){
                return '/api/service_referrer_chat/' + this.hash + '/:id/messages';
            },
            uploadsUrl(){
                return '/api/service_referrer_chat/' + this.hash + '/upload';
            },
            pusher_user_channel_name() {
                return 'private-' + this.pusher_common_channel_name + '-user-' + this.authenticated_userid;
            },
            pusher_common_channel_name() {
                return location.host.replace(/:/g, '-');
            },
        }
    })
</script>
</body>
</html>
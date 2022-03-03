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
    {!! \App\GlobalSettings::first()->head !!}
    <script src="https://kit.fontawesome.com/ceb594d2d9.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/vue@2.6.10/dist/vue.js"></script>
    <script src="https://unpkg.com/vuex@2.3.1/dist/vuex.js"></script>
    <script src="https://unpkg.com/vue-i18n@8.14.0/dist/vue-i18n.js"></script>
    <script src="https://unpkg.com/moment@2.24.0/moment.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/5.0/pusher.min.js"></script>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/vue-tel-input"></script>
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

    <div class="btn-toolbar" style="margin-left: auto; margin-right: auto; display: block;">
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

    <div class="login-form" v-if="has_telegram === null">
        <div class="form login-box">
            <div class="text-center">
                <i class="fab fa-telegram" style="color: rgb(73,168,233); font-size: 80px;"></i>
            </div>
            <h3 class="text-center" style="color: rgb(73,80,88);">@{{$t('is_telegram_installed')}}?</h3>       
            <div class="form-group">
                <button v-on:click="tg_yes" class="btn btn-success btn-block">@{{$t('yes')}}</button>
            </div> 
            <div class="form-group">
                <button v-on:click="tg_no" class="btn btn-warning btn-block">@{{$t('no')}}</button>
            </div> 
        </div>
    </div>

    <div class="login-form" v-if="has_telegram === true">
        <div class="form login-box">
            <h2 class="text-center" style="color: rgb(73,80,88);">@{{$t('access_to_chat')}}</h2>
            <div class="form-group">
                <vue-tel-input @@input="phone_input_change" @@input="register_error = false" v-model="phone" class="form-control" :placeholder="$t('phone')" required="required">
            </div>
            <div class="form-group">
                <input @@input="register_error = false" v-model="email" type="email" class="form-control" placeholder="someone@example.com" required="required">
            </div>
            <div class="form-group">
                <input @@input="register_error = false" v-model="name" name="name" type="text" class="form-control" placeholder="John Doe" required="required">
            </div>
            <div class="form-check">
                <input @@input="register_error = false" v-model="agree_with_policy" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="defaultCheck1" style="color: rgb(73,80,88);">
                    @{{$t('agree_with_policy')}}
                  </label>
            </div>
            <div class="form-check">
                <input @@input="register_error = false" v-model="agree_with_mailing" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="defaultCheck1" style="color: rgb(73,80,88);">
                    @{{$t('agree_with_mailing')}}
                  </label>
            </div>
        <p v-if="register_error" style="color: red;">@{{$t('register_error')}} @{{register_error_text}}</p>
            <div class="form-group">
                <button :disabled="!email || !phone || !name || agree_with_policy === false" v-on:click="register" class="btn btn-success btn-block">@{{$t('add_me')}}</button>
            </div> 
        </div>
        <p class="text-center"><a style="color: rgb(73,80,88);" v-on:click="changeForm()" href="#">@{{$t('back')}}</a></p>
    </div>

    <div class="login-form" v-if="has_telegram === false">
        <div class="form login-box">
            <div class="text-center">
                <i class="fab fa-telegram" style="color: rgb(73,168,233); font-size: 80px;"></i>
            </div>
            <h3 class="text-center" style="color: rgb(73,80,88);">@{{$t('what_is_phone')}}?</h3>
            <div class="text-center" style="color: rgb(73,80,88); font-size: 13px;">
                @{{$t('after_registration')}}
            </div>
            <div class="form-group">
                <a style="color: rgb(73,80,88);" target="_blank" href="https://telegram.org/dl/ios" class="btn btn-default btn-block">
                    <i class="fas fa-mobile" style="font-size: 25px; margin-right: 10px;"></i>
                    IPhone
                </a>
            </div> 
            <div class="form-group">
                <a style="color: rgb(73,80,88);" target="_blank" href="https://telegram.org/dl/android" class="btn btn-default btn-block">
                    <i class="fab fa-android" style="font-size: 25px; margin-right: 10px;"></i>
                    Android
                </a>
            </div> 
        </div>
        <p class="text-center"><a style="color: rgb(73,80,88);" v-on:click="changeForm()" href="#">@{{$t('back')}}</a></p>
    </div>
</div>

<script type="module">

    window.token = {!! json_encode($token) !!}; 

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
            'register_error': 'Ошибка данных. ',
            'agree_with_policy': 'Нажимая на кнопку регистрации, вы подтверждаете согласие с политикой конфиденциальности и обработки данных.',
            'agree_with_mailing' : 'Согласен на получение рассылки',
            'phone': 'Телефон',
            'is_telegram_installed': 'У вас уже установлен мессенджер Telegram?',
            'yes': 'Да, я уже установил и зарегистрировался',
            'no': 'Нет, скажите мне как',
            'access_to_chat': 'Для доступа в чат укажите ваши данные',
            'add_me': 'Добавьте меня',
            'back': 'Назад',
            'what_is_phone': 'Какой у вас телефон?',
            'after_registration': 'После скачивания приложения и регистрации, откройте заново эту страницу и получите доступ к чату.',
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
            'register_error': 'Server error. ',
            'agree_with_policy': 'By clicking on the register button, You confirm that you are agree with the processing of personal data and privacy policy.',
            'agree_with_mailing' : 'I agree to receive promotional information',
            'phone': 'Phone number',
            'is_telegram_installed': 'Do you already installed Telegram messenger?',
            'yes': 'Yes, I have already installed and registered',
            'no': 'No, tell me how',
            'access_to_chat': 'For accessing the chat please enter your data',
            'add_me': 'Add me',
            'back': 'Go back',
            'what_is_phone': 'What is your phone?',
            'after_registration': 'After downloading the application and finishing registration please open this page again and get access to the chat.',
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
            'register_error': 'Erro com servidor. ',
            'agree_with_policy': 'Ao clicar na tecla Registo, informo que concordo com o processamento de dados pessoais e política de privacidade.',
            'agree_with_mailing' : 'Aceito receber informações promocionais',
            'phone': 'Número de telefone',
            'is_telegram_installed': 'Você já instalou o Telegram messenger?',
            'yes': 'Sim, eu já instalei e registrei',
            'no': 'Não, diga-me como',
            'access_to_chat': 'Para acessar o chat, insira seus dados',
            'add_me': 'Adicionar me',
            'back': 'Voltar',
            'what_is_phone': 'Qual é o seu telefone?',
            'after_registration': 'Depois de baixar o aplicação e concluir o registro, abra esta página novamente e tenha acesso ao chat.',
        },
    };

    const i18n = new VueI18n({
        locale: 'pt',
        messages: locales,
    });

    Vue.use(VueTelInput)

    var app = new Vue({
        store,
        i18n,
        el: '#erp-application',
        props: [],
        data: {
            token: '',
            email: "",
            name: "",
            phone: "",
            formatted_phone: "",
            chat: false,
            users: [],
            login_error: false,
            register_error: false,
            register_error_text: "",
            agree_with_policy: true,
            agree_with_mailing: true,
            locale: 'pt',
            has_telegram: null,
        },
        mounted(){
            this.token = window.token;
        },
        methods: {
            changeForm(){
                this.has_telegram = null;
            },
            register(){
                if (this.password === this.password_confirm){
                    axios.post('/api/service_referrer/join_chat', 
                    {
                        "email": this.email, 
                        "name": this.name, 
                        "access_token": this.token,
                        'agree_with_mailing': this.agree_with_mailing,
                        'locale': this.locale,
                        'formatted_phone': this.formatted_phone,
                    }).
                    then(res => {
                        if (res.data.errcode == 1) {
                            this.register_error = true;
                        } 
                    }, 
                    res => {
                        this.register_error = true;
                    }
                );
                }
            },
            changeLang(loc){
                i18n.locale = loc;
                this.locale = loc;
            },
            tg_yes(){
                this.has_telegram = true;
            },
            tg_no(){
                this.has_telegram = false;
            },
            phone_input_change(number, value){
                this.formatted_phone = value.number.e164;
            }
        },
        computed: {
        }
    })
</script>
</body>
</html>
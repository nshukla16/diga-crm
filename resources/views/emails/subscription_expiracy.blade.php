<!DOCTYPE html>
<html>
    <head></head>
    <body>
        <header style="text-align: center; padding-bottom: 20px;">
            <img src="https://saas.diga.pt/img/logo_big.png" alt="DIGA LOGO" style="width: 140px;"/>
        </header>
        <section style="padding: 20px; background-image: url('https://saas.diga.pt/img/mail_background.png'); background-size: cover;">
            <div style="margin: 0 auto; width: 700px; background-color: #FFFFFF; padding: 20px;">
                <h2 style="text-align: center;">{{$number_of_days.trans('template.DaysLeft', [], $user->site_language) }}</h2>
                <div style="border: 1px solid #000000;"></div>
                <h4>{{trans('template.AfterTerm', [], $user->site_language)}}</h4>
                <ul>
                    @foreach ($modules as $mod)
                        <li>{{trans('template.module-'.$mod->name, [], $user->site_language)}}</li>
                    @endforeach
                </ul>
                <div style="text-align: center;">
                    <a style="
                        background-color:#2A6668;
                        border:1px solid #2A6668;
                        border-radius:3px;
                        color:#ffffff;
                        display:inline-block;
                        font-family:sans-serif;
                        font-size:16px;
                        line-height:44px;
                        text-align:center;
                        text-decoration:none;
                        width:250px;
                        -webkit-text-size-adjust:none;
                mso-hide:all;" href="{{env('APP_URL').'/subscriptions'}}">{{trans('template.GoToSubscription', [], $user->site_language)}}</a>
                </div>
            </div>
        </section>
        <footer>
            <table style="width: 700px;margin: 0 auto;">
                <tr>
                    <td style="text-align: left;">
                        &copy; {{ date('Y') }} Diga | Rua Prof. Mira Fernandes 20/21,<br/> 1600-381, Lisboa, Portugal | <a href="https://diga.pt/">diga.pt</a>
                    </td>
                    <td style="text-align: right;">
                        <a href="https://www.facebook.com/diga.pt/">Facebook</a>
                        <a href="https://www.instagram.com/diga.pt/">Instagram</a>
                        <a href="https://www.linkedin.com/company/diga-pt/">LinkedIn</a>
                    </td>
                </tr>
            </table>

        </footer>
    </body>
</html>